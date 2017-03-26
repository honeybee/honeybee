<?php

namespace Honeybee\Tests\Infrastructure\Fixture;

use Honeybee\Infrastructure\Fixture\FixtureService;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Fixture\FixtureInterface;
use Honeybee\Infrastructure\Fixture\FixtureList;
use Honeybee\Infrastructure\Fixture\FixtureTargetInterface;
use Honeybee\Infrastructure\Fixture\FixtureTargetMap;
use Honeybee\Model\Aggregate\AggregateRootType;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\Sham\DataGenerator;

class FixtureServiceTest extends TestCase
{
    public function testGetFixtureTargetMap()
    {
        $fixture_target_map = new FixtureTargetMap;
        $fixture_service = $this->makeFixtureService($fixture_target_map);

        $this->assertSame($fixture_target_map, $fixture_service->getFixtureTargetMap());
    }

    public function testGetFixtureTarget()
    {
        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $fixture_target_map = new FixtureTargetMap(['mock_fixture_target' => $mock_fixture_target]);
        $fixture_service = $this->makeFixtureService($fixture_target_map);

        $this->assertSame($mock_fixture_target, $fixture_service->getFixtureTarget('mock_fixture_target'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetFixtureTargetMissing()
    {
        $fixture_service = $this->makeFixtureService();
        $fixture_service->getFixtureTarget('mock_fixture_target');
    } // @codeCoverageIgnore

    public function testGetFixtureList()
    {
        $mock_fixture_list = Mockery::mock(FixtureList::CLASS);
        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $mock_fixture_target->shouldReceive('getFixtureList')->once()->withNoArgs()->andReturn($mock_fixture_list);
        $fixture_target_map = new FixtureTargetMap(['mock_fixture_target' => $mock_fixture_target]);
        $fixture_service = $this->makeFixtureService($fixture_target_map);

        $this->assertSame($mock_fixture_list, $fixture_service->getFixtureList('mock_fixture_target'));
    }

    public function testImport()
    {
        $mock_fixture = Mockery::mock(FixtureInterface::CLASS);
        $mock_fixture->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('mock_version');
        $mock_fixture->shouldReceive('getName')->once()->withNoArgs()->andReturn('name');
        $fixture_list = new FixtureList(['mock_version:name' => $mock_fixture]);
        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $mock_fixture_target->shouldReceive('getFixtureList')->once()->withNoArgs()->andReturn($fixture_list);
        $mock_fixture->shouldReceive('execute')->once()->with($mock_fixture_target);
        $fixture_target_map = new FixtureTargetMap(['mock_fixture_target' => $mock_fixture_target]);
        $fixture_service = $this->makeFixtureService($fixture_target_map);

        $this->assertSame($mock_fixture, $fixture_service->import('mock_fixture_target', 'mock_version:name'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testImportMissing()
    {
        $filtered_fixture_list = Mockery::mock(FixtureList::CLASS);
        $filtered_fixture_list->shouldReceive('count')->once()->withNoArgs()->andReturn(0);
        $mock_fixture_list = Mockery::mock(FixtureList::CLASS);
        $mock_fixture_list->shouldReceive('filter')->once()->with(Mockery::on(function ($arg) {
            $this->assertInstanceOf(\Closure::CLASS, $arg);
            return true;
        }))->andReturn($filtered_fixture_list);
        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $mock_fixture_target->shouldReceive('getFixtureList')->once()->withNoArgs()->andReturn($mock_fixture_list);
        $fixture_target_map = new FixtureTargetMap(['mock_fixture_target' => $mock_fixture_target]);
        $fixture_service = $this->makeFixtureService($fixture_target_map);

        $fixture_service->import('mock_fixture_target', 'mock_version:name');
    } // @codeCoverageIgnore

    public function testGenerate()
    {
        $fake_data1 = ['uuid' => '1234', 'language' => 'en_GB', 'version' => 1, 'created_at' => 'exclude'];
        $fake_data2 = ['uuid' => '4321', 'language' => 'en_GB', 'version' => 2, 'modified_at' => 'exclude'];
        $mock_aggregate_root_type = Mockery::mock(AggregateRootType::CLASS);
        $mock_aggregate_root_type->shouldReceive('getPrefix')->twice()->withNoArgs()->andReturn('mock_type');
        $aggregate_root_type_map = new AggregateRootTypeMap(['mock_type' => $mock_aggregate_root_type]);
        $mock_data_generator = Mockery::mock(DataGenerator::CLASS);
        $mock_data_generator->shouldReceive('createDataFor')->twice()->withArgs([
            $mock_aggregate_root_type,
            [
                'locale' => 'en_GB',
                'excluded_attributes' => [
                    'workflow_state',
                    'workflow_parameters',
                    'created_at',
                    'modified_at'
                ],
                'attribute_values' => [
                    'language' => 'en_GB',
                    'referenced_identifier' => '**REFERENCE ID REQUIRED**'
                ]
            ]
        ])->andReturn($fake_data1, $fake_data2);
        $fixture_service = $this->makeFixtureService(null, $aggregate_root_type_map, $mock_data_generator);

        $this->assertEquals(
            [
                [
                    'identifier' => 'mock_type-1234-en_GB-1',
                    'uuid' => '1234',
                    'language' => 'en_GB',
                    'version' => 1
                ],
                [
                    'identifier' => 'mock_type-4321-en_GB-2',
                    'uuid' => '4321',
                    'language' => 'en_GB',
                    'version' => 2
                ]
            ],
            $fixture_service->generate('mock_type', 2, 'en_GB')
        );
    }

    /**
     * @expectedException Trellis\Common\Error\RuntimeException
     */
    public function testGenerateMissingType()
    {
        $fixture_service = $this->makeFixtureService();
        $fixture_service->generate('missing');
    } // @codeCoverageIgnore

    private function makeFixtureService(
        $fixture_target_map = null,
        $aggregate_root_type_map = null,
        $data_generator = null
    ) {
        return new FixtureService(
            new ArrayConfig([]),
            $fixture_target_map ?: new FixtureTargetMap,
            $aggregate_root_type_map ?: new AggregateRootTypeMap,
            $data_generator ?: new DataGenerator
        );
    }
}
