<?php

namespace Honeybee\Ui\OutputFormat;

use Trellis\Common\Object;

class MediaTypeInfo extends Object implements MediaTypeInfoInterface
{
    protected $name;
    protected $title = '';
    protected $template = '';
    protected $template_alternatives = [];
    protected $type = '';
    protected $sub_type = '';
    protected $suffix = '';
    protected $optional_parameters = [];
    protected $required_parameters = [];
    protected $file_extensions = [];
    protected $abstract = '';
    protected $description = '';
    protected $references = [
        'RFC6838' => 'http://tools.ietf.org/html/rfc6838',
        'IANA' => 'http://www.iana.org/assignments/media-types/media-types.xhtml'
    ];
    protected $encoding_considerations = '';
    protected $security_considerations = '';

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getTemplateAlternatives()
    {
        return $this->template_alternatives;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubType()
    {
        return $this->sub_type;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getOptionalParameters()
    {
        return $this->optional_parameters;
    }

    public function getRequiredParameters()
    {
        return $this->required_parameters;
    }

    public function getFileExtensions()
    {
        return $this->file_extensions;
    }

    public function getAbstract()
    {
        return $this->abstract;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function getEncodingConsiderations()
    {
        return $this->encoding_considerations;
    }

    public function getSecurityConsiderations()
    {
        return $this->security_considerations;
    }
}
