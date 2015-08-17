<?php

namespace Honeybee\Model\Command\Bulk;

use Honeybee\Common\Error\ParseError;
use Honeybee\Common\Util\JsonToolkit;
use Iterator;

/**
 * Allows to traverse honeybee bulk-files containing bulk-operations.
 */
class BulkStreamIterator implements Iterator
{
    protected $position = 0;

    protected $file_pointer;

    protected $current_item = null;

    public function __construct($file_pointer)
    {
        // @todo validate $file_pointer, hence make sure it is readable etc.
        $this->file_pointer = $file_pointer;
    }

    public function current()
    {
        return $this->current_item;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $meta_data = $this->tryToReadMetaData();

        if ($meta_data instanceof BulkStreamError) {
            $this->current_item = $meta_data;
        } else {
            $payload = $this->tryToReadPayload();

            if ($payload instanceof BulkStreamError) {
                $this->current_item = $payload;
            } else {
                $this->current_item = new BulkOperation($meta_data, $payload);
                $this->position++;
            }
        }
    }

    public function rewind()
    {
        rewind($this->file_pointer);
        $this->next();
        $this->position = 0;
    }

    public function valid()
    {
        return $this->current_item instanceof BulkOperation;
    }

    protected function tryToReadMetaData()
    {
        $serialized_meta_data = fgets($this->file_pointer);
        if (!$serialized_meta_data) {
            return new BulkStreamError(
                BulkStreamError::EOF,
                "End of file reached, no more data to read."
            );
        }

        try {
            $meta_data = JsonToolkit::parse($serialized_meta_data);
        } catch (ParseError $parse_error) {
            return new BulkStreamError(
                BulkStreamError::INVALID_META_DATA,
                "Failed to parse meta-data for the given bulk-operation. Reason: " . $parse_error->getMessage()
            );
        }

        $required_keys = array('_type', '_identifier', '_command');
        $missing_keys = array();
        foreach ($required_keys as $required_key) {
            if (!isset($meta_data[$required_key])) {
                $missing_keys[] = $required_keys;
            }
        }

        if (count($missing_keys) > 0) {
            return new BulkStreamError(
                BulkStreamError::INVALID_META_DATA,
                sprintf(
                    "Missing expected key: %s within the meta-data of the given bulk-operation.",
                    $required_key
                )
            );
        }

        $command_implementor = $meta_data['_command'];
        if (!class_exists($command_implementor)) {
            return new BulkStreamError(
                BulkStreamError::INVALID_META_DATA,
                sprintf(
                    "Unable to resolve command implementor %s, given within bulk meta-data.",
                    $command_implementor
                )
            );
        }

        return new BulkMetaData($meta_data['_type'], $meta_data['_identifier'], $meta_data['_command']);
    }

    protected function tryToReadPayload()
    {
        $serialized_payload = fgets($this->file_pointer);
        if (!$serialized_payload) {
            return new BulkStreamError(
                BulkStreamError::INVALID_FORMAT,
                "Reached EOF, but was expecting payload for current meta-data of the given bulk-operation."
            );
        }

        try {
            return JsonToolkit::parse($serialized_payload);
        } catch (ParseError $parse_error) {
            return new BulkStreamError(
                BulkStreamError::INVALID_PAYLOAD,
                "Failed to parse payload for the given bulk-operation. Reason: " . $parse_error->getMessage()
            );
        }
    }
}
