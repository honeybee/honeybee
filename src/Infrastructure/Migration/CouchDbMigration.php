<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Infrastructure\DataAccess\Connector\ConnectableInterface;
use Honeybee\Common\Error\RuntimeError;
use Guzzle\Http\Exception\ClientErrorResponseException;

abstract class CouchDbMigration extends Migration
{
    const MAP_FILE_SUFFIX = '.map.js';

    const REDUCE_FILE_SUFFIX = '.reduce.js';

    abstract protected function getViewsDirectory();

    abstract protected function getDesignDocName();

    protected function createDatabaseIfNotExists(MigrationTarget $migration_target, $update_views = false)
    {
        if (!$this->databaseExists($migration_target)) {
            $this->createDatabase($migration_target, $update_views);
        } elseif ($update_views) {
            $this->updateDesignDoc($migration_target);
        }
    }

    protected function createDatabase(MigrationTarget $migration_target, $update_views = false)
    {
        try {
            $client = $this->getConnection($migration_target);
            $database_name = $this->getDatabaseName($migration_target);
            $response = $client->put('/' . $database_name)->send();
            if ($response->getStatusCode() !== 201) {
                throw new RuntimeError(
                    'Failed to create couchdb database %s. Received status %s along with this data: %s',
                    $database_name,
                    $response->getStatusCode(),
                    print_r($response->json(), true)
                );
            }
        } catch (ClientErrorResponseException $error) {
            $error_data = $error->getResponse()->json();
            throw new RuntimeError("Failed to create couchdb database. Reason: " . $error_data['reason']);
        }

        if ($update_views) {
            $this->updateDesignDoc($migration_target);
        }
    }

    protected function deleteDatabase(MigrationTarget $migration_target)
    {
        if ($this->databaseExists($migration_target)) {
            $client = $this->getConnection($migration_target);
            $database_name = $this->getDatabaseName($migration_target);
            $response = $client->delete('/' . $database_name)->send();
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeError(
                    'Failed to delete couchdb database %s. Received status %s along with this data: %s',
                    $database_name,
                    $response->getStatusCode(),
                    print_r($response->json(), true)
                );
            }
        }
    }

    protected function getDatabaseName(MigrationTarget $migration_target)
    {
        $connector = $migration_target->getTargetConnector();
        $connector_config = $connector->getConfig();

        return $connector_config->get('database');
    }

    protected function updateDesignDoc(MigrationTarget $migration_target)
    {
        $views_directory = $this->getViewsDirectory();
        if (!is_dir($views_directory)) {
            throw new RuntimeError(sprintf('Given views directory "%s" does not exist.', $views_directory));
        }

        $views = [];
        $glob_expression = sprintf('%s/*.map.js', $views_directory);
        foreach (glob($glob_expression) as $view_map_file) {
            $reduce_function = '';
            $map_function = file_get_contents($view_map_file);
            $view_name = str_replace(self::MAP_FILE_SUFFIX, '', basename($view_map_file));
            $views[$view_name] = [ 'map' => $map_function ];

            $reduce_file_path = dirname($view_map_file) . DIRECTORY_SEPARATOR . $view_name . self::REDUCE_FILE_SUFFIX;
            if (is_readable($reduce_file_path)) {
                $views[$view_name]['reduce'] = file_get_contents($reduce_file_path);
            }
        }

        $client = $this->getConnection($migration_target);
        $database_name = $this->getDatabaseName($migration_target);
        $document_path = sprintf('/%s/_design/%s', $database_name, urlencode($this->getDesignDocName()));

        try {
            $design_doc = $client->get($document_path)->send()->json();
        } catch (ClientErrorResponseException $error) {
            $error_data = $error->getResponse()->json();
            if ($error_data['error'] === 'not_found') {
                $design_doc = [];
            } else {
                throw $error;
            }
        }

        try {
            if (!empty($design_doc)) {
                $design_doc['views'] = $views;
                $payload = $design_doc;
            } else {
                $payload = [ 'language' => 'javascript', 'views' => $views ];
            }
            $client->put($document_path, [], json_encode($payload))->send();
        } catch (ClientErrorResponseException $error) {
            throw new RuntimeError("Failed to create/update couchdb design-doc. Reason: " . $error_data['reason']);
        }
    }

    protected function deleteDesignDoc(MigrationTarget $migration_target)
    {
        $client = $this->getConnection($migration_target);
        $database_name = $this->getDatabaseName($migration_target);
        $document_path = sprintf('/%s/_design/%s', $database_name, urlencode($this->getDesignDocName()));

        try {
            $cur_document = $client->get($document_path)->send()->json();
            $client->delete(sprintf('%s?rev=%s', $document_path, $cur_document['_rev']))->send()->json();
        } catch (ClientErrorResponseException $error) {
            $error_data = $error->getResponse()->json();
            if ($error_data['error'] !== 'not_found') {
                throw new RuntimeError("Failed to delete couchdb design-doc. Reason: " . $error_data['reason']);
            }
        }
    }

    protected function databaseExists(MigrationTargetInterface $migration_target)
    {
        try {
            $database_name = $this->getDatabaseName($migration_target);
            $client = $this->getConnection($migration_target);

            return $client->get('/' . $database_name)->send()->getStatusCode() === 200;
        } catch (ClientErrorResponseException $error) {
            $error_data = $error->getResponse()->json();
            if ($error_data['error'] === 'not_found') {
                return false;
            }
            throw $error;
        }
    }
}
