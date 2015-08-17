<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Honeybee\Infrastructure\Config\Settings;
use Trellis\Common\Object;

class StorageReaderIterator extends Object implements StorageReaderIteratorInterface
{
    protected $storage_reader;

    protected $current_data = [];

    protected $is_rewinded;

    protected $reader_settings;

    public function __construct(StorageReaderInterface $storage_reader, $limit = null)
    {
        $settings = [];
        if ($limit) {
            $settings['limit'] = $limit;
        }
        $this->reader_settings = new Settings($settings);
        $this->storage_reader = $storage_reader;

        $this->rewind();
    }

    public function current()
    {
        return current($this->current_data);
    }

    public function key()
    {
        return key($this->current_data);
    }

    public function next()
    {
        next($this->current_data);

        if (!$this->valid() && !empty($this->current_data)) {
            $this->current_data = $this->storage_reader->readAll($this->reader_settings);
        }

        $this->is_rewinded = false;
    }

    public function rewind()
    {
        if ($this->is_rewinded) {
            return;
        }

        $reader_settings = $this->reader_settings->toArray();
        $reader_settings['first'] = true;
        $this->current_data = $this->storage_reader->readAll(new Settings($reader_settings));

        $reader_settings['first'] = false;
        $this->reader_settings = new Settings($reader_settings);
        reset($this->current_data);

        $this->is_rewinded = true;
    }

    public function valid()
    {
        return false !== current($this->current_data);
    }
}
