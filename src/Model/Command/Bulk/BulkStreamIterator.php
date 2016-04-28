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
        $metadata = $this->tryToReadMetadata();

        if ($metadata instanceof BulkStreamError) {
            $this->current_item = $metadata;
        } else {
            $payload = $this->tryToReadPayload();

            if ($payload instanceof BulkStreamError) {
                $this->current_item = $payload;
            } else {
                $this->current_item = new BulkOperation($metadata, $payload);
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

    protected function tryToReadMetadata()
    {
        $serialized_metadata = fgets($this->file_pointer);
        if (!$serialized_metadata) {
            return new BulkStreamError(
                BulkStreamError::EOF,
                "End of file reached, no more data to read."
            );
        }

        try {
            $metadata = JsonToolkit::parse($serialized_metadata);
        } catch (ParseError $parse_error) {
            return new BulkStreamError(
                BulkStreamError::INVALID_METADATA,
                "Failed to parse metadata for the given bulk-operation. Reason: " . $parse_error->getMessage()
            );
        }

        $required_keys = array('_type', '_identifier', '_command');
        $missing_keys = array();
        foreach ($required_keys as $required_key) {
            if (!isset($metadata[$required_key])) {
                $missing_keys[] = $required_keys;
            }
        }

        if (count($missing_keys) > 0) {
            return new BulkStreamError(
                BulkStreamError::INVALID_METADATA,
                sprintf(
                    "Missing expected key: %s within the metadata of the given bulk-operation.",
                    $required_key
                )
            );
        }

        $command_implementor = $metadata['_command'];
        if (!class_exists($command_implementor)) {
            return new BulkStreamError(
                BulkStreamError::INVALID_METADATA,
                sprintf(
                    "Unable to resolve command implementor %s, given within bulk metadata.",
                    $command_implementor
                )
            );
        }

        return new BulkMetadata($metadata['_type'], $metadata['_identifier'], $metadata['_command']);
    }

    protected function tryToReadPayload()
    {
        $serialized_payload = fgets($this->file_pointer);
        if (!$serialized_payload) {
            return new BulkStreamError(
                BulkStreamError::INVALID_FORMAT,
                "Reached EOF, but was expecting payload for current metadata of the given bulk-operation."
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
