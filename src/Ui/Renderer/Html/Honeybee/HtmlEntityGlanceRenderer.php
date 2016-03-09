<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee;

use Honeybee\Ui\Renderer\Renderer;
use Honeybee\Ui\Renderer\EntityRenderer;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\ArrayToolkit;
use Honeybee\EntityInterface;
use Honeybee\Ui\ViewTemplate\ViewTemplateInterface;
use Trellis\Runtime\Attribute\AttributePath;
use Trellis\Runtime\Attribute\AttributeValuePath;
use Trellis\Runtime\Attribute\AttributePathInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\Attribute\Textarea\TextareaAttribute;
use Trellis\Runtime\Attribute\HandlesFileListInterface;
use Trellis\Runtime\Attribute\HandlesFileInterface;

class HtmlEntityGlanceRenderer extends EntityRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return $this->hasOption('view_template_name')
            ? 'html/entity_glance/as_fields_with_viewtemplate_without_tabs.twig'    // view_template
            : 'html/entity_glance/entity-glance.twig';  // default
    }

    protected function getTemplateParameters()
    {
        $params = parent::getDefaultTemplateParameters();

        $resource = $this->getPayload('subject');
        $parent_attribute = $resource->getType()->getParentAttribute();
        $group_parts = (array)$this->getOption('group_parts', []);

        $params['grouped_base_path'] = ArrayToolkit::flattenToArrayPath($group_parts);
        $params['is_embed_template'] = $this->getOption('is_embed_template', false);
        $params['has_parent_attribute'] = $parent_attribute !== null;
        $params['html_attributes'] = $this->getOption('html_attributes', []);
        $params['image_disabled'] = $this->getOption('image_disabled', false);
        $params['resource'] = $resource->toNative();
        $params['is_new'] = !$resource->hasValue('identifier');
        $params['css'] = $this->getOption('css', '');

        $params = array_replace_recursive($this->lookupViewTemplate(), $params);

        if ($this->hasOption('view_template_name')) {
            // use view_template
            $params['rendered_fields'] = $this->getRenderedFields($resource, $params['view_template']);
            $params['css'] .= $params['is_new'] ? ' hb-glance--empty' : null;
        } else {
            // get default values
            if (!$params['image_disabled']) {
                $image = $this->getGlanceImage($resource, $params['view_template']);

                $params['image_width'] = $this->getOption('image_width', $image['width']);
                $params['image_height'] = $this->getOption('image_height', $image['height']);
                $params['image_url'] = $image['location'];
            }
            $params['title'] = $this->getGlanceTitle($resource, $params['view_template']);
            $params['description'] = $this->getGlanceDescription($resource, $params['view_template']);
        }

        return $params;
    }

    protected function getGlanceImage(EntityInterface $resource, ViewTemplateInterface $view_template)
    {
        $image_default_attributes = [
            'location' => '',
            'width' => 100,
            'height' => 100
        ];

        // @todo 'image_url' should allow to restore the auto-retriving without specifying 'image_value_path',
        //       in the case a value was already set for it in a less specific config.
        if ($this->hasOption('image_url')) {
            $image_url = $this->getOption('image_url');
            if (!empty($image_url) || !$this->hasOption('image_value_path')) {
                // empty value would reset eventual global options and allow to use the value_path option
                return array_replace($image_default_attributes, [ 'location' => $image_url ]);
            }
        }

        $converjon_enabled = (bool)$this->getOption('use_converjon', false);

        $image_activity = null;
        if ($converjon_enabled) {
            $image_activity = $this->activity_service->getActivity(
                $this->getOption('image_activity_scope', 'converjon'),
                $this->getOption('image_activity_name', 'thumbnail')
            );

            $image_activity_url = $image_activity->getUrl();
            $image_default_attributes['width'] = $image_activity_url->getParameter('width');
            $image_default_attributes['height'] = $image_activity_url->getParameter('height');
        }

        // get value from configured value path
        if ($this->hasOption('image_value_path')) {
            $path = $this->getOption('image_value_path');
            $image_attribute = AttributePath::getAttributeByPath($resource->getType(), $path);
            if ($image_attribute instanceof HandlesFileListInterface
                && $image_attribute->getFiletypeName() === HandlesFileInterface::FILETYPE_IMAGE
            ) {
                $image_value = AttributeValuePath::getAttributeValueByPath($resource, $path);
                $index = $this->getOption('image_value_path_index', 0);
                if (array_key_exists($index, $image_value)) {
                    $image = $image_value[$index]->toNative();
                    $location = $image[$image_attribute->getFileLocationPropertyName()];

                    if ($converjon_enabled) {
                        $image_url = $this->url_generator->generateUrl(
                            $image_activity,
                            [ 'file' => $location ]
                        );
                        return array_replace($image_default_attributes, [ 'location' => $image_url ]);
                    } else {
                        $image_url = $this->url_generator->generateUrl(
                            'module.files.download',
                            [ 'resource' => $resource, 'file' => $location ]
                        );
                        return array_replace($image_default_attributes, [ 'location' => $image_url ]);
                    }
                }
            } else {
                throw new RuntimeError('Not supported at the moment. Please implement me.');
            }
        }


        // otherwise figure out a fallback value if there are attributes containing images
        $view_template_fields = $view_template->extractAllFields();
        foreach ($view_template_fields as $field) {
            $attribute_path = $field->getSetting('attribute_path');
            if ($attribute_path) {
                $attribute = AttributePath::getAttributeByPath($resource->getType(), $attribute_path);

                if ($attribute instanceof HandlesFileInterface
                    && $attribute->getFiletypeName() === HandlesFileInterface::FILETYPE_IMAGE) {
                    $image_value = $resource->getValue($attribute->getName());
                    if ($attribute instanceof HandlesFileListInterface) {
                        if (array_key_exists(0, $image_value)) {
                            $image = $image_value[0]->toNative();
                            $location = $image[$attribute->getFileLocationPropertyName()];

                            if ($converjon_enabled) {
                                $image_url = $this->url_generator->generateUrl(
                                    $image_activity,
                                    [ 'file' => $location ]
                                );
                                return array_replace($image_default_attributes, [ 'location' => $image_url ]);
                            } else {
                                $image_url = $this->url_generator->generateUrl(
                                    'module.files.download',
                                    [ 'resource' => $resource, 'file' => $location ]
                                );
                                return array_replace($image_default_attributes, [ 'location' => $image_url ]);
                            }
                        }
                    } elseif (!empty($image_value)) {
                        $image = $image_value->toNative();
                        $location = $image[$attribute->getFileLocationPropertyName()];

                        if ($converjon_enabled) {
                            $image_url = $this->url_generator->generateUrl(
                                $image_activity,
                                [ 'file' => $location ]
                            );
                            return array_replace($image_default_attributes, [ 'location' => $image_url ]);
                        } else {
                            $image_url = $this->url_generator->generateUrl(
                                'module.files.download',
                                [ 'resource' => $resource, 'file' => $location ]
                            );
                            return array_replace($image_default_attributes, [ 'location' => $image_url ]);
                        }
                    }
                }
            }
        }

        return $image_default_attributes;
    }

    protected function getGlanceTitle(EntityInterface $resource, ViewTemplateInterface $view_template)
    {
        if ($this->hasOption('title')) {
            $title = $this->getOption('title');
            // empty value would reset eventual global options and allow to use the value_path option
            if ($this->hasOption('title_value_path') && empty($title)) {
                return AttributeValuePath::getAttributeValueByPath($resource, $this->getOption('title_value_path'));
            } else {
                return $title;
            }
        }

        // otherwise get first text attribute value
        $view_template_fields = $view_template->extractAllFields();
        foreach ($view_template_fields as $field) {
            $attribute_path = $field->getSetting('attribute_path');
            if ($attribute_path) {
                $attribute = AttributePath::getAttributeByPath($resource->getType(), $attribute_path);
                if (in_array(get_class($attribute), [ TextAttribute::class ])) {
                    return AttributeValuePath::getAttributeValueByPath($resource, $attribute_path);
                }
            }
        }

        return '';
    }

    protected function getGlanceDescription(EntityInterface $resource, ViewTemplateInterface $view_template)
    {
        if ($this->hasOption('description')) {
            $description = $this->getOption('description');
            // empty value would reset eventual global options and allow to use the value_path option
            if ($this->hasOption('description_value_path') && empty($description)) {
                return AttributeValuePath::getAttributeValueByPath(
                    $resource,
                    $this->getOption('description_value_path')
                );
            } else {
                return $description;
            }
        }

        // otherwise get first textarea attribute value
        $view_template_fields = $view_template->extractAllFields();
        foreach ($view_template_fields as $field) {
            $attribute_path = $field->getSetting('attribute_path');
            if ($attribute_path) {
                $attribute = AttributePath::getAttributeByPath($resource->getType(), $attribute_path);
                if (in_array(get_class($attribute), [ TextareaAttribute::class ])) {
                    return AttributeValuePath::getAttributeValueByPath($resource, $attribute_path);
                }
            }
        }

        return '';
    }
}
