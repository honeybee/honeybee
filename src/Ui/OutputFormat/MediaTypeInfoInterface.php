<?php

namespace Honeybee\Ui\OutputFormat;

interface MediaTypeInfoInterface
{
    public function getName();

    public function getTitle();

    public function getTemplate();

    public function getTemplateAlternatives();

    public function getType();

    public function getSubType();

    public function getSuffix();

    public function getOptionalParameters();

    public function getRequiredParameters();

    public function getFileExtensions();

    public function getAbstract();

    public function getDescription();

    public function getReferences();

    public function getEncodingConsiderations();

    public function getSecurityConsiderations();
}
