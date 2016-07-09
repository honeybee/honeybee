<?php

namespace Honeybee\Ui\ViewTemplate;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;
use Honeybee\Ui\ViewTemplate\Part\Cell;
use Honeybee\Ui\ViewTemplate\Part\CellList;
use Honeybee\Ui\ViewTemplate\Part\Field;
use Honeybee\Ui\ViewTemplate\Part\FieldList;
use Honeybee\Ui\ViewTemplate\Part\Group;
use Honeybee\Ui\ViewTemplate\Part\GroupList;
use Honeybee\Ui\ViewTemplate\Part\Panel;
use Honeybee\Ui\ViewTemplate\Part\PanelList;
use Honeybee\Ui\ViewTemplate\Part\Row;
use Honeybee\Ui\ViewTemplate\Part\RowList;
use Honeybee\Ui\ViewTemplate\Part\Tab;
use Honeybee\Ui\ViewTemplate\Part\TabList;

class ViewTemplateService implements ViewTemplateServiceInterface
{
    protected $config;

    protected $view_templates_container_map;

    public function __construct(ConfigInterface $config, ViewTemplatesContainerMap $view_templates_container_map)
    {
        $this->config = $config;
        $this->view_templates_container_map = $view_templates_container_map;
    }

    /**
     * Returns the ViewTemplate instance for the given scope and name. In case an OutputFormat
     * is given and a output format specific view template exists for the given scope that
     * view template is returned.
     *
     * @param string $scope scope name of view template to get
     * @param string $view_template_name name non-empty view template name
     * @param OutputFormatInterface $output_format output format
     *
     * @return ViewTemplateInterface
     *
     * @throws RuntimeError in case the view template does not exist or the view template name is not a string or empty
     */
    public function getViewTemplate($scope, $view_template_name, OutputFormatInterface $output_format = null)
    {
        if (!is_string($view_template_name) || (is_string($view_template_name) && empty($view_template_name))) {
            throw new RuntimeError('View template name must be a non-empty string.');
        }

        if (!$this->view_templates_container_map->hasKey($scope)) {
            throw new RuntimeError('Unable to load view_templates-container for scope: ' . $scope);
        }
        $container = $this->view_templates_container_map->getItem($scope);
        // use output format specific view template name if output format was given and such a template exists
        if ($output_format !== null) {
            $specific_view_template_name = $view_template_name . '.' . $output_format->getName();
            if ($container->getViewTemplateMap()->hasKey($specific_view_template_name)) {
                $view_template_name = $specific_view_template_name;
            }
        }

        return $container->getViewTemplateByName($view_template_name);
    }

    /**
     * Returns whether there's a view template for the given scope and name. When an output format is
     * specified the method returns true when either an output format specific or the normal view
     * template name exists. When this method returns false the getViewTemplate will probably throw
     * an exception as there's no such view template to get.
     *
     * @param string $scope scope name of view template to check
     * @param string $name view template name
     * @param OutputFormatInterface $output_format output format
     *
     * @return boolean true if view template of that scope/name exists, false otherwise.
     */
    public function hasViewTemplate($scope, $view_template_name, OutputFormatInterface $output_format = null)
    {
        if (!$this->view_templates_container_map->hasKey($scope)) {
            return false;
        }

        $container = $this->view_templates_container_map->getItem($scope);

        // check for output format specific view template names if necessary
        if ($output_format !== null) {
            $specific_view_template_name = $view_template_name . '.' . $output_format->getName();

            return (
                $container->getViewTemplateMap()->hasKey($specific_view_template_name) ||
                $container->getViewTemplateMap()->hasKey($view_template_name)
            );
        }

        return $container->getViewTemplateMap()->hasKey($view_template_name);
    }

    /**
     * Returns all known scopes from the view_templates config.
     *
     * @return array all view_templates scopes configured
     */
    public function getViewTemplateScopes()
    {
        return $this->view_templates_container_map->getKeys();
    }

    /**
     * Returns the names of all configured view templates for the given scope.
     *
     * @param string $scope view_templates scope name
     *
     * @return array all names of view_template instances from the given scope
     */
    public function getViewTemplateNames($scope)
    {
        return $this->view_templates_container_map->getItem($scope)->getViewTemplateMap()->getKeys();
    }

    /**
     * Creates a ViewTemplate instance with one tab, one panel, one row, one list, one group and as many
     * fields as the resource type has attributes. To only include specific attributes as fields set
     * them in the attribute_names method argument.
     *
     * @param string $view_template_name name of the created view template
     * @param ProjectionTypeInterface $resource_type
     * @param array $attribute_names list of attributes to include as view template fields; if empty all
     *                               attributes will be included as fields
     *
     * @return ViewTemplateInterface instance with all or specified attributes as fields
     *
     * @throws RuntimeError in case of an empty view template name
     */
    public function createViewTemplate(
        $view_template_name,
        ProjectionTypeInterface $resource_type,
        array $attribute_names = []
    ) {
        if (empty($view_template_name)) {
            throw new RuntimeError('A view template name must be specified.');
        }

        $field_list = new FieldList();
        foreach ($resource_type->getAttributes() as $attribute_name => $attribute) {
            if (empty($attribute_names)) {
                $field_list->addItem(new Field($attribute_name));
            } elseif (in_array($attribute_name, $attribute_names)) {
                $field_list->addItem(new Field($attribute_name));
            }
        }

        $group_list = new GroupList();
        $group_list->addItem(new Group('group-content', $field_list));

        $cell_list = new CellList();
        $cell_list->addItem(new Cell($group_list));

        $row_list = new RowList();
        $row_list->addItem(new Row($cell_list));

        $panel_list = new PanelList();
        $panel_list->addItem(new Panel('panel-content', $row_list));

        $tab_list = new TabList();
        $tab_list->addItem(new Tab('tab-content', $panel_list));

        $view_template = new ViewTemplate($view_template_name, $tab_list);

        return $view_template;
    }
}
