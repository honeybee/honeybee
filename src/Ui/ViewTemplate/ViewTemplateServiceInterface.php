<?php

namespace Honeybee\Ui\ViewTemplate;

use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;

interface ViewTemplateServiceInterface
{
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
    public function getViewTemplate($scope, $view_template_name, OutputFormatInterface $output_format = null);

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
    public function hasViewTemplate($scope, $view_template_name, OutputFormatInterface $output_format = null);

    /**
     * Returns all known scopes from the view_templates config.
     *
     * @return array all view_templates scopes configured
     */
    public function getViewTemplateScopes();

    /**
     * Returns the names of all configured view templates for the given scope.
     *
     * @param string $scope view_templates scope name
     *
     * @return array all names of view_template instances from the given scope
     */
    public function getViewTemplateNames($scope);

    /**
     * Creates a ViewTemplate instance with one tab, one panel, one row, one list, one group and as many
     * fields as the resource type has attributes. To only include specific attributes as fields
     * set them in the attribute_names method argument.
     *
     * @param string $view_template_name name of the created view template
     * @param ProjectionTypeInterface $resource_type
     * @param array $attribute_names list of attributes to include as view template fields; if empty all
     *                               attributes will be included as fields
     *
     * @return ViewTemplateInterface instance with all or specified attributes as fields
     */
    public function createViewTemplate(
        $view_template_name,
        ProjectionTypeInterface $resource_type,
        array $attribute_names = []
    );
}
