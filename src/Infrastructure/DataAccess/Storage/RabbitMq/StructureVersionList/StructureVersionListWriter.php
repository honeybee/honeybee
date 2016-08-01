<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\RabbitMq\StructureVersionList;

use Assert\Assertion;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\RabbitMq\RabbitMqStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\Migration\StructureVersion;
use Honeybee\Infrastructure\Migration\StructureVersionList;

class StructureVersionListWriter extends RabbitMqStorage implements StorageWriterInterface
{
    public function write($structure_version_list, SettingsInterface $settings = null)
    {
        Assertion::isInstanceOf($structure_version_list, StructureVersionList::CLASS);

        $exchange = $this->getConfig()->get('exchange');
        $channel = $this->connector->getConnection()->channel();

        // delete existing bindings by identifier & arguments
        foreach ($structure_version_list as $structure_version) {
            $this->delete(
                $structure_version_list->getIdentifier(),
                new Settings([ 'arguments' => $this->buildArguments($structure_version) ])
            );
        }

        // recreate all the bindings
        foreach ($structure_version_list as $structure_version) {
            $channel->exchange_bind(
                $exchange,
                $exchange,
                $structure_version_list->getIdentifier(),
                false,
                $this->buildArguments($structure_version)
            );
        }
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        $arguments = $settings->get('arguments');

        Assertion::isInstanceOf($arguments, SettingsInterface::CLASS);

        $exchange = $this->getConfig()->get('exchange');
        $channel = $this->connector->getConnection()->channel();
        $channel->exchange_unbind($exchange, $exchange, $identifier, false, $arguments->toArray());
    }

    protected function buildArguments(StructureVersion $structure_version)
    {
        return [
            '@type' => [ 'S', get_class($structure_version) ],
            'target_name' => [ 'S', $structure_version->getTargetName() ],
            'version' => [ 'S', $structure_version->getVersion() ],
            'created_date' => [ 'S', $structure_version->getCreatedDate() ]
        ];
    }
}
