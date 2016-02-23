<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee;

use Honeybee\Ui\Renderer\Renderer;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\EntityInterface;
use Trellis\Runtime\Attribute\AttributePath;
use Trellis\Runtime\Attribute\AttributeValuePath;
use Trellis\Runtime\Attribute\AttributePathInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\Attribute\Textarea\TextareaAttribute;
use Trellis\Runtime\Attribute\HandlesFileListInterface;
use Trellis\Runtime\Attribute\HandlesFileInterface;

class HtmlEntityGlanceRenderer extends Renderer
{
    protected function validate()
    {
        if (!$this->getPayload('subject') instanceof EntityInterface) {
            throw new RuntimeError(sprintf('Payload "subject" must implement "%s".', EntityInterface::CLASS));
        }
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        return 'html/ui/entity-glance.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $resource = $this->getPayload('subject');
        $glance_image = $this->getGlanceImage($resource);

        $params['image_width'] = $this->getOption('image_width', $glance_image['width']);
        $params['image_height'] = $this->getOption('image_height', $glance_image['height']);
        $params['image_url'] = $glance_image['location'];
        $params['title'] = $this->getGlanceTitle($resource);
        $params['description'] = $this->getGlanceDescription($resource);
        $params['css'] = $this->getOption('css', '');

        return $params;
    }

    protected function getGlanceImage(EntityInterface $resource)
    {
        $image_default_attributes = [
            'location' => '',
            'width' => 100,
            'height' => 100
        ];

        $glance_image_url = $this->getOption('image_url');
        if (!empty($glance_image_url) || !$this->hasOption('image_value_path')) {
            // empty value would reset eventual global options and allow to use the value_path option
            return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
        }

        $converjon_enabled = (bool)$this->getOption('use_converjon', false);

        $glance_image_activity = null;
        if ($converjon_enabled) {
            $glance_image_activity = $this->activity_service->getActivity(
                $this->getOption('image_activity_scope', 'converjon'),
                $this->getOption('image_activity_name', 'thumbnail')
            );

            $glance_image_activity_url = $glance_image_activity->getUrl();
            $image_default_attributes['width'] = $glance_image_activity_url->getParameter('width');
            $image_default_attributes['height'] = $glance_image_activity_url->getParameter('height');
        }

        // get value from configured value path
        if ($this->hasOption('image_value_path')) {
            $path = $this->getOption('image_value_path');
            $image_attribute = AttributePath::getAttributeByPath($resource->getType(), $path);
            if ($image_attribute instanceof HandlesFileListInterface && $image_attribute->getFiletypeName() === HandlesFileInterface::FILETYPE_IMAGE) {
                $image_value = AttributeValuePath::getAttributeValueByPath($resource, $path);
                $index = $this->getOption('image_value_path_index', 0);
                if (array_key_exists($index, $image_value)) {
                    $image = $image_value[$index]->toNative();
                    $location = $image[$image_attribute->getFileLocationPropertyName()];

                    if ($converjon_enabled) {
                        $glance_image_url = $this->url_generator->generateUrl(
                            $glance_image_activity,
                            [ 'file' => $location ]
                        );
                        return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
                    } else {
                        $glance_image_url = $this->url_generator->generateUrl(
                            'module.files.download',
                            [ 'resource' => $resource, 'file' => $location ]
                        );
                        return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
                    }
                }
            } else {
                throw new RuntimeError('Not supported at the moment. Please implement me.');
            }
        }

        // figure out a fallback value if there are attributes containing images
        $image_attributes = $resource->getType()->getAttributes(); // @todo Support class intefaces as getAttributes parameter
        if (!empty($image_attributes)) {
            foreach ($image_attributes as $image_attribute) {
                if (!$image_attribute instanceof HandlesFileInterface) {
                    continue;
                }
                if ($image_attribute->getFiletypeName() !== HandlesFileInterface::FILETYPE_IMAGE) {
                    continue;
                }
                $image_value = $resource->getValue($image_attribute->getName());
                if ($image_attribute instanceof HandlesFileListInterface) {
                    if (array_key_exists(0, $image_value)) {
                        $image = $image_value[0]->toNative();
                        $location = $image[$image_attribute->getFileLocationPropertyName()];

                        if ($converjon_enabled) {
                            $glance_image_url = $this->url_generator->generateUrl(
                                $glance_image_activity,
                                [ 'file' => $location ]
                            );
                            return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
                        } else {
                            $glance_image_url = $this->url_generator->generateUrl(
                                'module.files.download',
                                [ 'resource' => $resource, 'file' => $location ]
                            );
                            return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
                        }
                    }
                } elseif (!empty($image_value)) {
                    $image = $image_value->toNative();
                    $location = $image[$image_attribute->getFileLocationPropertyName()];

                    if ($converjon_enabled) {
                        $glance_image_url = $this->url_generator->generateUrl(
                            $glance_image_activity,
                            [ 'file' => $location ]
                        );
                        return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
                    } else {
                        $glance_image_url = $this->url_generator->generateUrl(
                            'module.files.download',
                            [ 'resource' => $resource, 'file' => $location ]
                        );
                        return array_replace($image_default_attributes, [ 'location' => $glance_image_url ]);
                    }
                }
            }
        }

        return $image_default_attributes;
    }

    protected function getGlanceTitle(EntityInterface $resource)
    {
        if ($this->hasOption('title')) {
            $glance_title = $this->getOption('title');
            // empty value would reset eventual global options and allow to use the value_path option
            if ($this->hasOption('title_value_path') && empty($glance_title)) {
                return AttributeValuePath::getAttributeValueByPath($resource, $this->getOption('title_value_path'));
            } else {
                return $glance_title;
            }
        }

        // otherwise get first text attribute value
        $text_attributes = $resource->getType()->getAttributes([], [ TextAttribute::class ])->toArray();
        if (!empty($text_attributes)) {
            $target_text_attribute = array_keys($text_attributes)[0];
            return $resource->getValue($target_text_attribute);
        }

        return '';
    }

    protected function getGlanceDescription(EntityInterface $resource)
    {
        if ($this->hasOption('description')) {
            $glance_description = $this->getOption('description');
            // empty value would reset eventual global options and allow to use the value_path option
            if ($this->hasOption('description_value_path') && empty($glance_description)) {
                return AttributeValuePath::getAttributeValueByPath($resource, $this->getOption('description_value_path'));
            } else {
                return $glance_description;
            }
        }

        // otherwise get first textarea attribute value
        $textarea_attributes = $resource->getType()->getAttributes([], [ TextareaAttribute::class ])->toArray();
        if (!empty($textarea_attributes)) {
            $target_textarea_attribute = array_keys($textarea_attributes)[0];
            return $resource->getValue($target_textarea_attribute);
        }

        return '';
    }
}
