<?php

namespace Honeybee\Common\Util;

class ClassFileInfo
{
    protected $class_file_path;

    protected $class_name;

    protected $namespace;

    public function __construct(array $state = [])
    {
        foreach ($state as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    public function getFilePath()
    {
        return $this->class_file_path;
    }

    public function getFileName()
    {
        return basename($this->class_file_path);
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getClassName()
    {
        return $this->class_name;
    }

    public function getFullyQualifiedClassName()
    {
        return empty($this->namespace) ? $this->class_name : $this->namespace . '\\' . $this->class_name;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
