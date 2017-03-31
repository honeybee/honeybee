<?php

namespace Honeybee\Tests\Infrastructure\Job;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Event\FailedJobEvent;
use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\Infrastructure\Job\JobMap;
use Honeybee\Infrastructure\Job\JobService;
use Honeybee\Infrastructure\Job\Strategy\Retry\RetryStrategyInterface;
use Honeybee\ServiceLocatorInterface;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\NullLogger;
use Mockery;

class JobServiceTest extends TestCase
{
    protected $mock_service_locator;

    protected $mock_connector;

    protected $mock_connection;

    protected $mock_channel;

    protected $mock_job;

    protected $mock_event_bus;

    protected $mock_closure;

    public function setUp()
    {
        $this->mock_service_locator = Mockery::mock(ServiceLocatorInterface::CLASS);
        $this->mock_connector = Mockery::mock(RabbitMqConnector::CLASS);
        $this->mock_connection = Mockery::mock(AbstractConnection::CLASS);
        $this->mock_channel = Mockery::mock(AbstractChannel::CLASS);
        $this->mock_job = Mockery::mock(JobInterface::CLASS);
        $this->mock_event_bus = Mockery::mock(EventBusInterface::CLASS);
        $this->mock_closure = function () {
        }; // @codeCoverageIgnore
    }

    public function testDispatch()
    {
        $expected = $this->getAMQPMessage([ 'data' => 'state' ]);
        $this->mock_channel->shouldReceive('basic_publish')
            ->once()
            ->with(
                Mockery::on(
                    function (AMQPMessage $message) use ($expected) {
                        $this->assertEquals($expected, $message);
                        return true;
                    }
                ),
                'exchange',
                'route'
            );
        $this->mock_connection->shouldReceive('channel')->once()->andReturn($this->mock_channel);
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_connection);

        $this->mock_job->shouldReceive('toArray')->andReturn([ 'data' => 'state' ]);
        $this->mock_job->shouldReceive('getSettings')->andReturn(new Settings([ 'routing_key' => 'route' ]));
        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            $job_map = new JobMap([ 'job' => [ 'state' ] ]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertEquals($job_map, $job_service->getJobMap());
        $this->assertEquals(new Settings([ 'state' ]), $job_service->getJob('job'));
        $this->assertNull($job_service->dispatch($this->mock_job, 'exchange'));
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testDispatchInvalidExchange()
    {
        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->mock_job->shouldReceive('getSettings')->andReturn(new Settings([ 'routing_key' => 'route' ]));
        $job_service->dispatch($this->mock_job, []);
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testDispatchInvalidRoutingKey()
    {
        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->mock_job->shouldReceive('getSettings')->andReturn(new Settings([ 'routing_key' => 7 ]));
        $job_service->dispatch($this->mock_job, 'exchange');
    } // @codeCoverageIgnore

    public function testConsume()
    {
        $this->mock_channel->shouldReceive('basic_qos')->once()->with(null, 1, null);
        $this->mock_channel->shouldReceive('basic_consume')
            ->once()
            ->with('queue', false, true, false, false, false, $this->mock_closure);
        $this->mock_connection->shouldReceive('channel')->once()->andReturn($this->mock_channel);
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_connection);

        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertEquals($this->mock_channel, $job_service->consume('queue', $this->mock_closure));
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testConsumeEmptyQueue()
    {
        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertEquals($this->mock_channel, $job_service->consume('', $this->mock_closure));
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testConsumeInvalidQueue()
    {
        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertEquals($this->mock_channel, $job_service->consume(9, $this->mock_closure));
    } // @codeCoverageIgnore

    public function testRetry()
    {
        $expected = $this->getAMQPMessage(
            [ 'job' => 'state', 'metadata' => [ 'retries' => 1 ] ],
            [ 'delivery_mode' => 2, 'expiration' => 123 ]
        );
        $this->mock_channel
            ->shouldReceive('basic_publish')
            ->once()
            ->with(
                Mockery::on(
                    function (AMQPMessage $message) use ($expected) {
                        $this->assertEquals($expected, $message);
                        return true;
                    }
                ),
                'exchange',
                'route'
            );
        $this->mock_connection->shouldReceive('channel')->once()->andReturn($this->mock_channel);
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_connection);
        $this->mock_strategy = Mockery::mock(RetryStrategyInterface::CLASS);
        $this->mock_strategy->shouldReceive('getRetryInterval')->once()->andReturn(123);
        $this->mock_job->shouldReceive('getSettings')->once()->andReturn(new Settings([ 'routing_key' => 'route' ]));
        $this->mock_job->shouldReceive('toArray')->once()->andReturn([ 'job' => 'state' ]);
        $this->mock_job->shouldReceive('getStrategy')->once()->andReturn($this->mock_strategy);

        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($job_service->retry($this->mock_job, 'exchange', []));
    }

    public function testFail()
    {
        $expected = new FailedJobEvent([
            'failed_job_state' => [ 'job' => 'state' ],
            'metadata' => [ 'message' => 'fail' ]
        ]);
        $this->mock_job->shouldReceive('toArray')->once()->andReturn([ 'job' => 'state' ]);
        $this->mock_event_bus
            ->shouldReceive('distribute')
            ->once()
            ->with(
                'honeybee.events.failed',
                Mockery::on(
                    function (EventInterface $event) use ($expected) {
                        $expected = array_merge(
                            $expected->toArray(),
                            [ 'uuid' => $event->getUuid(), 'iso_date' => $event->getIsoDate() ]
                        );
                        $this->assertEquals($expected, $event->toArray());
                        return true;
                    }
                )
            );

        $job_service = new JobService(
            $this->mock_connector,
            $this->mock_service_locator,
            $this->mock_event_bus,
            new JobMap([]),
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($job_service->fail($this->mock_job, [ 'message' => 'fail' ]));
    }

    protected function getAMQPMessage(array $payload, array $options = [])
    {
        return new AMQPMessage(
            json_encode($payload),
            array_merge([ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ], $options)
        );
    }
}
