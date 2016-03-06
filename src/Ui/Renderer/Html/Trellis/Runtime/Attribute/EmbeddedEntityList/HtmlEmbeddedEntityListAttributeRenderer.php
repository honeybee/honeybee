<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\EmbeddedEntityList;

use Trellis\Runtime\Entity\EntityInterface;
use Trellis\Runtime\Attribute\ListAttribute;
use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Ui\Activity\Activity;
use Honeybee\Ui\Activity\Url;
use Honeybee\Ui\Activity\ActivityMap;

class HtmlEmbeddedEntityListAttributeRenderer extends HtmlAttributeRenderer
{
    const RENDER_GLANCE = true;
    const GLANCE_CONFIG_GLOBAL_SCOPE = 'application';

    protected $glance_config;
    protected $entity_type_glance_configs;

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/embedded-entity-list/as_input.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();
        $embedded_entity_list = $this->determineAttributeValue($this->attribute->getName());

        $rendered_entities = [];
        foreach ($embedded_entity_list as $pos => $embedded_entity) {
            $rendered_entities[] = $this->renderEmbeddedEntity($embedded_entity, $pos);
        }

        $params['rendered_entities'] = $rendered_entities;
        $params['embedded_type_names'] = $this->attribute->getEmbeddedEntityTypeMap()->getKeys();
        $params['rendered_embed_selector'] = $this->renderEmbeddedEntityTypeSelectorMap();
        $params['rendered_type_templates'] = $this->renderEmbeddedTypeTemplates($embedded_entity_list->getSize());
        $params['inline_mode'] = $this->attribute->getOption('inline_mode', false);

        if ($this->attribute->getOption('inline_mode', false)) {
            $params['css'] .= ' hb-entity-list__inline-mode';
        }

        return $params;
    }

    protected function renderEmbeddedTypeTemplates($start_position)
    {
        $rendered_type_templates = [];
        $embed_position = $start_position;

        foreach ($this->attribute->getEmbeddedEntityTypeMap() as $embedded_type) {
            $type_prefix = $embedded_type->getPrefix();
            $rendered_type_templates[$type_prefix] = $this->renderEmbeddedEntity(
                $embedded_type->createEntity([], $this->getPayload('resource')),
                $embed_position,
                true
            );
            $embed_position++;
        }

        return $rendered_type_templates;
    }

    protected function renderEmbeddedEntityTypeSelectorMap()
    {
        $embed_type_selector_map = new ActivityMap();
        $selector_td = $this->getDefaultTranslationDomain();
        foreach ($this->attribute->getEmbeddedEntityTypeMap()->getKeys() as $embedded_type_prefix) {
            $embed_type_selector_map->setItem(
                $embedded_type_prefix,
                new Activity([
                    'name' => $embedded_type_prefix,
                    'label' => $this->_($embedded_type_prefix, $selector_td), // @todo translate stuff
                    'url' => Url::createUri('#' . $embedded_type_prefix),
                    'settings' => new Settings
                ])
            );
        }

        $view_scope = $this->getOption('view_scope');
        $default_settings = [ 'view_scope' => $view_scope ];
        $renderer_config = $this->view_config_service->getRendererConfig(
            $view_scope,
            $this->output_format,
            $embed_type_selector_map,
            $default_settings
        );

        return $this->renderer_service->renderSubject($embed_type_selector_map, $this->output_format, $renderer_config);
    }

    protected function renderEmbeddedEntity(EntityInterface $embedded_entity, $position, $is_embed_template = false)
    {
        $view_scope = $this->getOption('view_scope');
        $group_parts = array_merge(
            (array)$this->getOption('group_parts', []),
            [ $this->attribute->getName(), $position ]
        );

        $default_settings = [
            'view_scope' => $view_scope,
            'group_parts' => $group_parts,
            'is_embed_template' => $is_embed_template,
            'expand_content_disabled' => $this->getOption('expand_items_content_disabled', false),
            'expand_content_by_default' => $this->getOption('expand_items_content_by_default', false),
        ];
        if ($is_embed_template) {
            $default_settings['expand_content_by_default'] = $this->getOption('expand_content_for_new_items', true);
        }

        $renderer_config = $this->view_config_service->getRendererConfig(
            $view_scope,
            $this->output_format,
            $embedded_entity,
            $default_settings
        );

        $renderer_settings = [
            'add_item_to_parent_list_allowed' => $this->isAddItemAllowed(),
            'readonly' => $this->isReadonly()
        ];
        if (static::RENDER_GLANCE) {
            $renderer_settings['glance_config'] = $this->getGlanceRenderConfig($embedded_entity);
        }

        return $this->renderer_service->renderSubject(
            $embedded_entity,
            $this->output_format,
            $renderer_config,
            [],
            Settings::createFromArray($renderer_settings)
        );
    }

    /**
     * @return ArrayConfig
     */
    protected function getGlanceRenderConfig(EntityInterface $entity, $additional_config = [])
    {
        $view_scope = $this->getOption('view_scope');
        $entity_type = $entity->getType();

        if (!$this->glance_config instanceof ConfigInterface) {
            // global view_config
            $global_view_config = $this->view_config_service->getViewConfig(self::GLANCE_CONFIG_GLOBAL_SCOPE);
            $global_view_config_settings = $global_view_config->getSettings();
            $global_glance_config = $global_view_config_settings->get('glance_config', new Settings);

            // view_config for specific view
            $view_config = $this->view_config_service->getViewConfig($view_scope);
            $view_config_settings = $view_config->getSettings();
            $view_glance_config = $view_config_settings->get('glance_config', new Settings);

            // view_template settings for current embedded-entity-list
            $list_glance_config = $this->settings->get('glance_config', new Settings);

            $this->glance_config = new ArrayConfig(array_replace_recursive(
                $global_glance_config->toArray(),
                $view_glance_config->toArray(),
                $list_glance_config->toArray()
            ));
        }

        // view_config for resource type.
        $type_name = $entity_type->getName();
        if (!isset($this->entity_type_glance_configs[ $type_name ])) {
            $renderer_config_entity_type = $this->view_config_service->getRendererConfig(
                $view_scope,
                $this->output_format,
                $entity_type->getScopeKey()
            );

            $this->entity_type_glance_configs[$type_name] = $renderer_config_entity_type->get(
                'glance_config',
                new Settings
            );
        }

        return new ArrayConfig(array_replace_recursive(
            $this->glance_config->toArray(),
            $this->entity_type_glance_configs[ $type_name ]->toArray(),
            $additional_config
        ));
    }

    protected function determineAttributeValue($attribute_name, $default_value = '')
    {
        $embedded_entity_list = clone $this->getPayload('resource')->getValue($attribute_name);

        if ($this->attribute->getOption('inline_mode', false)) {
            $served_types = [];
            foreach ($embedded_entity_list as $entity) {
                $served_types[] = $entity->getType()->getPrefix();
            }
            foreach ($this->attribute->getEmbeddedEntityTypeMap()->getValues() as $embed_type) {
                if (!in_array($embed_type->getPrefix(), $served_types)) {
                    $embedded_entity_list->push($embed_type->createEntity([], $this->getPayload('resource')));
                }
            }
        }

        return $embedded_entity_list;
    }


    protected function isRequired()
    {
        $is_required = parent::isRequired();

        $list_attribute = clone $this->determineAttributeValue($this->attribute->getName());

        // check options against actual value
        $items_number = count($list_attribute);
        $min_count = $this->getMinCount($is_required);

        if (is_numeric($min_count) && $items_number < $min_count) {
            $is_required = true;
        }

        return $is_required;
    }

    protected function getWidgetOptions()
    {
        $widget_options = parent::getWidgetOptions();

        $resource = $this->getPayload('resource');

        if ($resource->hasValue('identifier')) {
            $render_embed_uri_tpl = $this->url_generator->generateUrl(
                'module.resource.embed',
                [ 'resource' => $resource, 'embed_path' => '{EMBED_PATH}' ],
                [ 'relative' => true ]
            );
        } else {
            $render_embed_uri_tpl = $this->url_generator->generateUrl(
                'module.embed',
                [ 'module' => $resource->getType(), 'embed_path' => '{EMBED_PATH}' ],
                [ 'relative' => true ]
            );
        }

        $widget_options['min_count'] = $this->getMinCount($this->isRequired());
        $widget_options['max_count'] = $this->getMaxCount();
        $widget_options['inline_mode'] = $this->attribute->getOption('inline_mode', false);
        $widget_options['input_group'] = (array)$this->getOption('group_parts', []);
        $widget_options['fieldname'] = $this->attribute->getName();
        $widget_options['render_embed_uri_tpl'] = $render_embed_uri_tpl;

        return $widget_options;
    }

    // if the list attribute is required it needs at least one item
    protected function getMinCount($is_required = false)
    {
        $min_count = $this->getOption('min_count', $this->attribute->getOption(ListAttribute::OPTION_MIN_COUNT));

        if (!is_numeric($min_count) && $is_required) {
            $min_count = 1;
        }

        return $min_count;
    }

    protected function getMaxCount()
    {
        return $this->getOption('max_count', $this->attribute->getOption(ListAttribute::OPTION_MAX_COUNT));
    }

    protected function isAddItemAllowed()
    {
        $list_attribute = $this->determineAttributeValue($this->attribute->getName());
        $items_number = count($list_attribute);
        $max_count = $this->getMaxCount();

        if (is_numeric($max_count) && $items_number >= $max_count) {
            return false;
        }
        return true;
    }

    protected function getWidgetImplementor()
    {
        return 'jsb_Honeybee_Core/ui/EmbeddedEntityList';
    }
}
