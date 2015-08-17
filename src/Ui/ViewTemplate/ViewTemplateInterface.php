<?php

namespace Honeybee\Ui\ViewTemplate;

interface ViewTemplateInterface
{
    public function getName();

    public function getTabList();

    public function getTab($name);

    /**
     * Returns all fields independant of the groups they are in.
     *
     * @return array of all fields as FieldInterface instances
     */
    public function extractAllFields();
}
