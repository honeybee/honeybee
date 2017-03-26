<?php

namespace Honeybee\Tests\Infrastructure\Event\Bus;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Event\Bus\Transport\JobQueueTransport;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\Tests\TestCase;
use Mockery;
use Honeybee\Infrastructure\Job\JobInterface;

class JobQueueTransportTest extends TestCase
{
    public function testConstruct()
    {
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $this->assertEquals('jobs', $transport->getName());
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testConstructEmptyName()
    {
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('', $mock_job_service, 'exchange');
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testConstructInvalidName()
    {
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport(7, $mock_job_service, 'exchange');
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testConstructInvalidExchange()
    {
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, []);
    } // @codeCoverageIgnore

    public function testSend()
    {
        $mock_job = Mockery::mock(JobInterface::CLASS);
        $mock_event_interface = Mockery::mock(EventInterface::CLASS);
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $mock_job_service->shouldReceive('createJob')
            ->once()
            ->with(
                [
                    'event' => $mock_event_interface,
                    'channel' => 'channel',
                    'subscription_index' => 1,
                    'metadata' => [ 'job_name' => 'myjob' ]
                ]
            )
            ->andReturn($mock_job);
        $mock_job_service->shouldReceive('dispatch')->once()->with($mock_job, 'exchange');

        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $this->assertNull($transport->send('channel', $mock_event_interface, 1, new Settings([ 'job' => 'myjob' ])));
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testSendEmptyChannel()
    {
        $mock_event_interface = Mockery::mock(EventInterface::CLASS);
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $transport->send('', $mock_event_interface, 1, new Settings);
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testSendInvalidChannel()
    {
        $mock_event_interface = Mockery::mock(EventInterface::CLASS);
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $transport->send(8, $mock_event_interface, 1, new Settings);
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testSendInvalidSubscription()
    {
        $mock_event_interface = Mockery::mock(EventInterface::CLASS);
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $transport->send('channel', $mock_event_interface, '', new Settings);
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testSendMissingJob()
    {
        $mock_event_interface = Mockery::mock(EventInterface::CLASS);
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $transport->send('channel', $mock_event_interface, 1, new Settings);
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testSendInvalidJob()
    {
        $mock_event_interface = Mockery::mock(EventInterface::CLASS);
        $mock_job_service = Mockery::mock(JobServiceInterface::CLASS);
        $transport = new JobQueueTransport('jobs', $mock_job_service, 'exchange');
        $transport->send('channel', $mock_event_interface, 1, new Settings([ 'job' => '' ]));
    } // @codeCoverageIgnore
}
