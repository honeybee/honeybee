<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\JsonToolkit;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;

abstract class ElasticsearchMigration extends Migration
{
    const SCROLL_SIZE = 1000;

    const SCROLL_TIMEOUT = '30s';

    abstract protected function getIndexSettingsPath(MigrationTargetInterface $migration_target);

    /**
     * Must return an associative array for elasticsearch type mappings. Array key is the name of the
     * elasticsearch type mapping and value is the mapping itself.
     */
    abstract protected function getTypeMappingPaths(MigrationTargetInterface $migration_target);

    protected function createIndexIfNotExists(MigrationTarget $migration_target, $register_type_mapping = false)
    {
        $index_api = $this->getConnection($migration_target)->indices();
        $params = [ 'index' => $this->getIndexName($migration_target) ];
        if (!$index_api->exists($params) && !$this->getAliasMapping($migration_target)) {
            $this->createIndex($migration_target, $register_type_mapping);
        } else {
            $this->updateMappings($migration_target);
        }
    }

    protected function createIndex(MigrationTarget $migration_target, $register_type_mapping = false)
    {
        $index_api = $this->getConnection($migration_target)->indices();
        $index_api->create(
            $this->getIndexSettings($migration_target, $register_type_mapping)
        );
    }

    protected function deleteIndex(MigrationTarget $migration_target)
    {
        $index_api = $this->getConnection($migration_target)->indices();
        $params = [ 'index' => $this->getIndexName($migration_target) ];
        if ($index_api->exists($params)) {
            $index_api->delete($params);
        }
    }

    protected function updateMappings(MigrationTarget $migration_target, $reindex_if_required = false)
    {
        $index_api = $this->getConnection($migration_target)->indices();
        $index_name = $this->getIndexName($migration_target);
        $reindex_required = false;

        $index_per_type = $migration_target->getTargetConnector()->getConfig()->get('index_per_type', false);
        foreach ($this->getTypeMappings($migration_target) as $type_name => $mapping) {
            try {
                $payload = [
                    'index' => $index_name,
                ];
                if ($index_per_type) {
                    $payload['body'] = $mapping;
                } else {
                    // elasticsearch 1-6
                    $payload['type'] = $type_name;
                    $payload['body'] = [ $type_name => $mapping ];
                }
                $index_api->putMapping($payload);
                if ($index_per_type) {
                    // only one type is relevant in elasticsearch 7+
                    break;
                }
            } catch (BadRequest400Exception $error) {
                if (!$reindex_if_required) {
                    throw $error;
                }
                $reindex_required = true;
            }
        }

        if (true === $reindex_required && true === $reindex_if_required) {
            $this->updateMappingsWithReindex($migration_target);
        }
    }

    protected function updateMappingsWithReindex(MigrationTarget $migration_target)
    {
        $client = $this->getConnection($migration_target);
        $index_api = $client->indices();
        $index_name = $this->getIndexName($migration_target);
        $aliases = $this->getAliasMapping($migration_target);

        if (count($aliases) > 1) {
            throw new RuntimeError(sprintf(
                'Aborting reindexing because there is more than one index mapped to the alias: %s',
                $index_name
            ));
        }

        $current_index = key($aliases);
        $new_index = sprintf('%s_%s', $index_name, $this->getTimestamp());

        // Allow index settings override
        $index_settings = $this->getIndexSettings($migration_target);
        $index_settings = $index_settings['body'] ?: current($index_api->getSettings([ 'index' => $index_name ]));

        // Load existing mappings from previous index
        $index_mappings = current($index_api->getMapping([ 'index' => $index_name ]));

        $index_per_type = $migration_target->getTargetConnector()->getConfig()->get('index_per_type', false);
        if ($index_per_type) {
            // elasticsearch 7+ (one type per index)

            // Merge mappings from new index settings if provided
            if (isset($index_settings['mappings']['properties'])) {
                $index_mappings['mappings']['properties'] = array_replace(
                    $index_mappings['mappings']['properties'],
                    $index_settings['mappings']['properties']
                );
                unset($index_settings['mappings']);
            }

            // Replace existing mappings with new ones
            $new_mappings = $this->getTypeMappings($migration_target);
            if (empty($new_mappings)) {
                throw new RuntimeError('No new type mappings provided for reindexing?');
            }
            if (count($new_mappings) !== 1) {
                throw new RuntimeError('Only one type mapping per index allowed in elasticsearch 7+.');
            }
            $new_mapping = reset($new_mappings);
            $index_mappings['mappings']['properties'] = array_replace(
                $index_mappings['mappings']['properties'],
                $new_mapping['properties']
            );
        } else {
            // behaviour for elasticsearch 1-6 w/ multiple types per index

            // Merge mappings from new index settings if provided
            if (isset($index_settings['mappings'])) {
                foreach ($index_settings['mappings'] as $type_name => $mapping) {
                    $index_mappings['mappings'] = array_replace(
                        $index_mappings['mappings'],
                        [ $type_name => $mapping ]
                    );
                }
                unset($index_settings['mappings']);
            }

            // Replace existing mappings with new ones
            foreach ($this->getTypeMappings($migration_target) as $type_name => $mapping) {
                $index_mappings['mappings'] = array_replace(
                    $index_mappings['mappings'],
                    [ $type_name => $mapping ]
                );
            }
        }

        // Create the new index
        $index_api->create([
            'index' => $new_index,
            'body' => array_merge($index_settings, $index_mappings)
        ]);

        // Copy documents from current index to new index
        $response = $client->search([
            'search_type' => 'scan',
            'scroll' => self::SCROLL_TIMEOUT,
            'size' => self::SCROLL_SIZE,
            'index'=> $current_index
        ]);
        $scroll_id = $response['_scroll_id'];
        $total_docs = $response['hits']['total'];

        while (true) {
            $response = $client->scroll([ 'scroll_id' => $scroll_id, 'scroll' => self::SCROLL_TIMEOUT ]);
            if (count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $document) {
                    $meta = [
                        '_index' => $new_index,
                        '_type' => $document['_type'],
                        '_id' => $document['_id']
                    ];
                    if ($index_per_type) {
                        unset($meta['_type']);
                    }
                    $bulk[]['index'] = $meta;
                    $bulk[] = $document['_source'];
                }
                $client->bulk([ 'body' => $bulk ]);
                unset($bulk);
                $scroll_id = $response['_scroll_id'];
            } else {
                break;
            }
        }

        // Check reindexed document count is correct
        $index_api->flush();
        $new_count = $client->count([ 'index' => $new_index ])['count'];
        if ($total_docs !== $new_count) {
            throw new RuntimeError(sprintf(
                'Aborting migration because document count of %s after reindexing does not match expected count of %s',
                $new_count,
                $total_docs
            ));
        }

        // Switch aliases from old to new index
        $actions = [
            [ 'remove' => [ 'alias' => $index_name, 'index' => $current_index ] ],
            [ 'add' => [ 'alias' => $index_name, 'index' => $new_index ] ]
        ];
        $index_api->updateAliases([ 'body' => [ 'actions' => $actions ] ]);
    }

    protected function updateIndexTemplates(MigrationTarget $migration_target, array $templates)
    {
        $index_api = $this->getConnection($migration_target)->indices();
        foreach ($templates as $template_name => $template_file) {
            if (!is_readable($template_file)) {
                throw new RuntimeError(sprintf('Unable to read index template at: %s', $template_file));
            }
            $template = JsonToolkit::parse(file_get_contents($template_file));
            $index_api->putTemplate([ 'name' => $template_name, 'body' => $template]);
        }
    }

    protected function createSearchTemplates(MigrationTarget $migration_target, array $templates)
    {
        $client = $this->getConnection($migration_target);
        foreach ($templates as $template_name => $template_file) {
            if (!is_readable($template_file)) {
                throw new RuntimeError(sprintf('Unable to read search template at: %s', $template_file));
            }
            $client->putTemplate(
                [
                    'id' => $template_name,
                    'body' => file_get_contents($template_file)
                ]
            );
        }
    }

    protected function getIndexSettings(MigrationTarget $migration_target, $include_type_mapping = false)
    {
        $settings_json_file = $this->getIndexSettingsPath($migration_target);

        if (empty($settings_json_file)) {
            return [];
        }

        if (!is_readable($settings_json_file)) {
            throw new RuntimeError(sprintf('Unable to read settings for index at: %s', $settings_json_file));
        }

        // Index is created with migration timestamp suffix and aliased in order to support
        // zero down-time migrations
        $index_name = $this->getIndexName($migration_target);
        $index_settings['index'] = sprintf('%s_%s', $index_name, $this->getTimestamp());
        $index_settings['body'] = JsonToolkit::parse(file_get_contents($settings_json_file));
        $index_settings['body']['aliases'][$index_name] = new \stdClass();

        if ($include_type_mapping) {
            $type_mappings = $this->getTypeMappings($migration_target);
            if (isset($index_settings['body']['mappings'])) {
                $index_settings['body']['mappings'] = array_merge(
                    $index_settings['body']['mappings'],
                    $type_mappings
                );
            } else {
                $index_settings['body']['mappings'] = $type_mappings;
            }
        }

        return $index_settings;
    }

    protected function getAliasMapping(MigrationTargetInterface $migration_target)
    {
        $aliases = [];
        $index_api = $this->getConnection($migration_target)->indices();

        try {
            $aliases = $index_api->getAlias([ 'name' => $this->getIndexName($migration_target) ]);
        } catch (Missing404Exception $error) {
        }

        return $aliases;
    }

    /**
     * Returns configured 'index' name. When 'index_per_type' is set as setting on connector for
     * elasticsearch7+ support the index name will have the mapping 'type' appended: "index-type".
     */
    protected function getIndexName(MigrationTargetInterface $migration_target)
    {
        $index_name = $migration_target->getConfig()->get('index');
        $mappings = $this->getTypeMappings($migration_target);
        if (empty($mappings)) {
            return $index_name;
        }

        // new setting 'index_per_type' (on connector/client) for elasticsearch7+ support
        $index_per_type = $migration_target->getTargetConnector()->getConfig()->get('index_per_type', false);
        if (!$index_per_type) {
            return $index_name; // elasticsearch 1-6 behaviour w/ potentially multiple types per index
        }

        if (count($mappings) !== 1) {
            throw new RuntimeError('Please use only one type mapping for an elasticsearch 7+ index.');
        }
        reset($mappings);
        $type_name = key($mappings);
        return $index_name.'-'.$type_name;
    }

    /**
     * Returns configured type name => mapping entries. When 'index_per_type' is set as setting on
     * connector for elasticsearch7+ support the index name will have the specified type mapping name
     * appended: "index-type". Please don't use "_doc" as a type mapping name. "_doc" will be used as
     * the endpoint for document types in elasticsearch 7+ URLs. The type name returned in this
     * method is more of a convention for different types used within the application.
     */
    protected function getTypeMappings(MigrationTarget $migration_target)
    {
        $mappings = [];
        $paths = (array) $this->getTypeMappingPaths($migration_target);

        foreach ($paths as $type_name => $mapping_file) {
            if (!is_readable($mapping_file)) {
                throw new RuntimeError(sprintf('Unable to read type-mapping at: %s', $mapping_file));
            }
            $mappings[$type_name] = JsonToolkit::parse(file_get_contents($mapping_file));
        }

        return $mappings;
    }
}
