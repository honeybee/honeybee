<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Projection\ProjectionList;

abstract class EntityListRenderer extends Renderer
{
    protected function validate()
    {
        if (!$this->getPayload('subject') instanceof ProjectionList) {
            throw new RuntimeError(
                sprintf('Payload "subject" must implement "%s".', ProjectionList::CLASS)
            );
        }
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/resource_collection/as_itemlist.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $resource_collection = $this->getPayload('subject');

        $scope = $this->getOption('view_scope', 'default.collection');

        $default_data = [
            'view_scope' => $scope, // e.g. honeybee.system_account.user.collection
        ];

        $rendered_resources = [];
        foreach ($resource_collection as $resource) {
            $renderer_config = $this->view_config_service->getRendererConfig(
                $scope,
                $this->output_format,
                $resource,
                $default_data
            );

            $rendered_resources[] = $this->renderer_service->renderSubject(
                $resource,
                $this->output_format,
                $renderer_config,
                [],
                $this->settings
            );
        }

        $params['rendered_resources'] = $rendered_resources;

        $view_template_name = $this->getOption('view_template_name', 'default.collection');
        if (!$this->hasOption('view_template_name')) {
            $view_template_name = $this->name_resolver->resolve($resource_collection);
        }

        $params['header_fields'] = $this->getOption('header_fields', []);
        if (!$this->hasOption('header_fields')) {
            /*
             * Header fields are not necessary in all cases, but are used if a table like display is used.
             * The header may be given as renderer config or render settings or they will be taken from a
             * specific header_fields view template or the view template for the first resource of the list.
             *
             * This flexibility is necessary to support cases of a table with header fields and different
             * resource types in the actual resource collection (like a search returning docs of different types).
             */
            $hfvtn = $view_template_name . '.header_fields';
            if ($this->view_template_service->hasViewTemplate($scope, $hfvtn, $this->output_format)) {
                $view_template = $this->view_template_service->getViewTemplate($scope, $hfvtn, $this->output_format);
                $params['header_fields'] = $view_template->extractAllFields();
            } elseif (!$resource_collection->isEmpty() && !$resource_collection->containsMultipleTypes()) {
                $resource = $resource_collection->getFirst();
                $vtn = $this->name_resolver->resolve($resource);
                if ($this->view_template_service->hasViewTemplate($scope, $vtn, $this->output_format)) {
                    $view_template = $this->view_template_service->getViewTemplate($scope, $vtn, $this->output_format);
                    $params['header_fields'] = $view_template->extractAllFields();
                }
            } else {
                /*
                 * when resource_collection is not empty and contains multiple types we could throw an exception
                 * to force devs to specify header_fields when the twig template uses them?
                 */
                $params['header_fields'] = [];
            }
        }

        return $params;
    }
}
