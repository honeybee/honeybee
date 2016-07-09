<?php

namespace Honeybee\Ui\ViewTemplate;

use Honeybee\Ui\ViewTemplate\Part\TabList;

class ViewTemplate implements ViewTemplateInterface
{
    protected $name;

    protected $tab_list;

    public function __construct($name, TabList $tab_list)
    {
        $this->name = $name;
        $this->tab_list = $tab_list;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTabList()
    {
        return $this->tab_list;
    }

    public function getTab($name)
    {
        return $this->tab_list->getByName($name);
    }

    public function extractAllFields()
    {
        $fields = [];

        foreach ($this->tab_list as $tab) {
            foreach ($tab->getPanelList() as $panel) {
                foreach ($panel->getRowList() as $row) {
                    foreach ($row->getCellList() as $cell) {
                        foreach ($cell->getGroupList() as $group) {
                            foreach ($group->getFieldList() as $field) {
                                $fields[$field->getName()] = $field;
                            }
                        }
                    }
                }
            }
        }

        return $fields;
    }
}
