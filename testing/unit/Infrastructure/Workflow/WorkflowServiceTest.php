<?php

namespace Honeybee\Tests\Infrastructure\Workflow;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Workflow\StateMachineBuilderInterface;
use Honeybee\Infrastructure\Workflow\WorkflowService;
use Honeybee\Infrastructure\Workflow\WorkflowServiceInterface;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\Author;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType as AuthorProjectionType;
use Honeybee\Tests\TestCase;
use Psr\Log\LoggerInterface;

class WorkflowServiceTest extends TestCase
{
    public function testDefaultWriteEventNamesAreDefined()
    {
        $this->assertSame(
            [ 'promote', 'demote', 'delete' ],
            $this->getDefaultWorkflowService()->getWriteEventNames()
        );
    }

    public function testDefaultWriteEventNamesCanBeDefined()
    {
        $this->assertEquals(
            [ 'foo', 'bar' ],
            $this->getDefaultWorkflowService([ 'write_event_names' => [ 'foo', 'bar' ] ])->getWriteEventNames()
        );
    }

    public function testStringStateMachineNameResolvingWorks()
    {
        $this->assertInstanceOf(WorkflowServiceInterface::CLASS, $this->getDefaultWorkflowService());
        $this->assertEquals('asdf', $this->getDefaultWorkflowService()->resolveStateMachineName('asdf'));
    }

    public function testAggregateRootTypeStateMachineNameResolvingWorks()
    {
        $author_type = new AuthorType();
        $this->assertEquals('honeybee_cmf.aggregate_fixtures.author', $author_type->getPrefix());
        $this->assertEquals(
            'honeybee_cmf.aggregate_fixtures.author.default_workflow',
            $this->getDefaultWorkflowService()->resolveStateMachineName($author_type)
        );
    }

    public function testAggregateRootStateMachineNameResolvingWorks()
    {
        $author = (new AuthorType())->createEntity();
        $this->assertEquals(
            'honeybee_cmf.aggregate_fixtures.author.default_workflow',
            $this->getDefaultWorkflowService()->resolveStateMachineName($author)
        );
    }

    public function testProjectionTypeStateMachineNameResolvingWorks()
    {
        $this->assertEquals(
            'honeybee_cmf.projection_fixtures.author.default_workflow',
            $this->getDefaultWorkflowService()->resolveStateMachineName(new AuthorProjectionType())
        );
    }

    public function testProjectionStateMachineNameResolvingWorks()
    {
        $author = (new AuthorProjectionType())->createEntity();
        $this->assertEquals(
            'honeybee_cmf.projection_fixtures.author.default_workflow',
            $this->getDefaultWorkflowService()->resolveStateMachineName($author)
        );
    }

    /**
     * @dataProvider provideInvalidStateMachineNames
     *
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testStateMachineNameResolvingThrowsOnArray($what)
    {
        $this->getDefaultWorkflowService()->resolveStateMachineName($what);
    } // @codeCoverageIgnore

    /**
     * @codeCoverageIgnore
     */
    public function provideInvalidStateMachineNames()
    {
        return [
            [ [] ],
            [ [[]] ],
            [ ['a' => 'b'] ],
            [ new \stdClass() ],
            [ null ],
            [ '' ],
            [ ' ' ],
        ];
    }

    protected function getDefaultWorkflowService(array $config = [])
    {
        $smb_stub = $this->createMock(StateMachineBuilderInterface::CLASS);
        $logger_stub = $this->createMock(LoggerInterface::CLASS);

        return new WorkflowService(new ArrayConfig($config), $smb_stub, $logger_stub);
    }
}
