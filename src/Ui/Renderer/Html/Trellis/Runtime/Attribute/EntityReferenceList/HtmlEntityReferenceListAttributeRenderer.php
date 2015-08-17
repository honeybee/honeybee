<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\EntityReferenceList;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\EntityTypeInterface;
use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\EmbeddedEntityList\HtmlEmbeddedEntityListAttributeRenderer;

class HtmlEntityReferenceListAttributeRenderer extends HtmlEmbeddedEntityListAttributeRenderer
{
    const SUGGEST_VALUE_IDENTIFIER = 'identifier';
    const SUGGEST_VALUE_ATTRIBUTE = 'referenced_identifier';

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/entity-reference-list/as_input.twig';
    }

    protected function getWidgetOptions()
    {
        $options = parent::getWidgetOptions();

        $options['suggest_options'] = $this->buildSuggestOptions();
        $options['initial_value'] = $this->buildInitialWidgetValue($options['suggest_options']);

        return $options;
    }

    protected function buildInitialWidgetValue(array $suggest_options)
    {
        $widget_value = [];
        foreach ($this->determineAttributeValue($this->attribute->getName()) as $embedded_entity) {
            $embedded_type = $embedded_entity->getType()->getPrefix();
            if (!isset($suggest_options[$embedded_type])) {
                throw new RuntimeError('Missing suggest configuration for embed-type: ' . $embedded_type);
            }
            $embedded_type_opts = $suggest_options[$embedded_type];
            $suggest_field = $embedded_type_opts['suggest_field'];
            $widget_value[] = [
                'value' => $embedded_entity->getValue(self::SUGGEST_VALUE_ATTRIBUTE),
                'label' => $embedded_entity->getValue($suggest_field)
            ];
        }

        return $widget_value;
    }

    protected function buildSuggestOptions()
    {
        $suggest_options = [];
        $resource = $this->getPayload('resource');
        $resource_type = $resource->getType();

        foreach ($this->attribute->getEmbeddedEntityTypeMap() as $embedded_type) {
            $display_fields = $this->fetchDisplayFields($embedded_type);
            $suggest_fieldname = $this->getSuggestFieldname($embedded_type, reset($display_fields));
            if (!in_array($suggest_fieldname, $display_fields)) {
                array_unshift($display_fields, $suggest_fieldname);
            }

            $referenced_type = $this->resource_type_map->getByClassName($embedded_type->getReferencedTypeClass());
            if ($resource->hasValue('identifier')) {
                $render_embed_uri_tpl = $this->genUrl(
                    'module.resource.embed',
                    [ 'resource' => $resource, 'embed_path' => '{EMBED_PATH}' ],
                    [ 'relative' => true ]
                );
            } else {
                $render_embed_uri_tpl = $this->genUrl(
                    'module.embed',
                    [ 'module' => $resource_type, 'embed_path' => '{EMBED_PATH}' ],
                    [ 'relative' => true ]
                );
            }
            $suggest_options[$embedded_type->getPrefix()] = [
                'display_fields' => $display_fields,
                'suggest_field' => $suggest_fieldname,
                'value_field' => self::SUGGEST_VALUE_IDENTIFIER,
                'placeholder' => $this->_(
                    sprintf('search for a %s by %s', $referenced_type->getName(), $suggest_fieldname)
                ),
                'render_uri_tpl' => $render_embed_uri_tpl,
                'suggest_url' => $this->genUrl(
                    'module.suggestions',
                    [
                        'module' => $referenced_type,
                        'display_fields' => implode(',', $display_fields),
                        'search' => sprintf('suggest:%s=', $suggest_fieldname)
                    ],
                    [ 'relative' => true ]
                )
            ];
        }

        return $suggest_options;
    }

    protected function fetchDisplayFields(EntityTypeInterface $embedded_type)
    {
        $resource = $this->getPayload('resource');
        $resource_type = $resource->getType()->getRoot();
        $resource_type_prefix = $resource_type->getPrefix();
        $embedded_type_prefix = $embedded_type->getPrefix();
        $scope = $this->getOption('view_scope', 'default.resource');
        $template_name = sprintf(
            '%s.%s.%s',
            $resource_type_prefix,
            $this->attribute->getPath(),
            $embedded_type_prefix
        );

        $view_template = $this->view_template_service->getViewTemplate($scope, $template_name, $this->output_format);

        $display_fields = [];
        foreach ($view_template->extractAllFields() as $field) {
            if ($embedded_type->hasAttribute($field->getName())) {
                $attribute = $embedded_type->getAttribute($field->getName());
                if ($attribute->getOption('mirrored', false)) {
                    $display_fields[] = $attribute->getName();
                }
            }
        }

        return $display_fields;
    }

    protected function getSuggestFieldname(EntityTypeInterface $embedded_type, $default_fieldname)
    {
        $type_prefix = $embedded_type->getPrefix();
        $suggest_field_option = $type_prefix . '.suggest_attribute';

        if ($this->hasOption($suggest_field_option)) {
            $suggest_fieldname = $this->getOption($suggest_field_option);
            if (!$embedded_type->hasAttribute($suggest_fieldname)) {
                throw new RuntimeError(
                    sprintf(
                        'Non-existant suggest_attribute "%s" configured for embed-reference-type: %s',
                        $suggest_fieldname,
                        $embedded_type->getName()
                    )
                );
            }
        } else {
            $suggest_fieldname = $default_fieldname;
        }

        return $suggest_fieldname;
    }

    protected function getWidgetImplementor()
    {
        return 'jsb_Honeybee_Core/ui/EntityReferenceList';
    }
}
