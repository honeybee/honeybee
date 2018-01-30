<?php

namespace Honeybee\Common\Util;

use Trellis\Common\BaseObject;

class ClassFileInfo extends BaseObject
{
    protected $class_file_path;

    protected $class_name;

    protected $namespace;

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
}
