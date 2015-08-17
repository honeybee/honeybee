<?php

namespace Honeybee\Ui\OutputFormat;

use Trellis\Common\Object;

class OutputFormat extends Object implements OutputFormatInterface
{
    protected $name;
    protected $renderer_locator = '';
    protected $content_type = '';
    protected $acceptable_content_types = [];
    protected $media_type_info = [];

    public function getName()
    {
        return $this->name;
    }

    public function getRendererLocator()
    {
        return $this->renderer_locator;
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    public function getAcceptableContentTypes()
    {
        return $this->acceptable_content_types;
    }

    public function getMediaTypeInfo()
    {
        return $this->media_type_info;
    }
}
