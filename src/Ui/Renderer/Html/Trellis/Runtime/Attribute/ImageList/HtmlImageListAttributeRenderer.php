<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\ImageList;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlImageListAttributeRenderer extends HtmlAttributeRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/image-list/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $resource = $this->getPayload('resource');

        $thumbnail_activity = null;
        $preview_activity = null;
        if ($this->getOption('use_converjon', false)) {
            $thumbnail_activity = $this->activity_service->getActivity(
                $this->getOption('thumbnail_activity_scope', 'converjon'),
                $this->getOption('thumbnail_activity_name', 'thumbnail')
            );
            $preview_activity = $this->activity_service->getActivity(
                $this->getOption('preview_activity_scope', 'converjon'),
                $this->getOption('preview_activity_name', 'preview')
            );
        }

        $images = [];
        $root_doc = $resource->getRoot() ?: $resource;
        foreach ($params['attribute_value'] as $image) {
            $original_image_url = $this->url_generator->generateUrl(
                'module.files.download',
                [ 'resource' => $root_doc, 'file' => $image->getLocation() ]
            );

            $additional_file_info = [
                'id' => md5($image->getLocation()),
                'thumb_url' => $original_image_url,
                'preview_url' => $original_image_url,
                'download_url' => $original_image_url
            ];

            if ($this->getOption('use_converjon', false)) {
                $url_params = [ 'file' => $image->getLocation() ];
                $additional_file_info['thumb_url'] = $this->url_generator->generateUrl(
                    $thumbnail_activity,
                    $url_params
                );
                $additional_file_info['preview_url'] = $this->url_generator->generateUrl(
                    $preview_activity,
                    $url_params
                );
            }

            $images[] = array_merge($image->toNative(), $additional_file_info);
        }

        $upload_input_name = $this->getOption('form-name', 'uploadform') . '[' . $this->attribute->getPath() . ']';

        $params['images'] = $images;
        $params['resource_type_prefix'] = $this->attribute->getRootType()->getPrefix();
        $params['resource_type_name'] = $root_doc->getType()->getName();
        $params['resource_identifier'] = $root_doc->getIdentifier();
        $params['upload_input_name'] = $upload_input_name;
        $params['upload_url'] = $this->url_generator->generateUrl('module.files.upload', [ 'resource' => $root_doc ]);

        return $params;
    }

    protected function determineAttributeValue($attribute_name, $default_value = [])
    {
        $value = [];

        if ($this->hasOption('value')) {
            return (array)$this->getOption('value', $default_value);
        }

        $expression = $this->getOption('expression');
        if (!empty($expression)) {
            $value = $this->evaluateExpression($expression);
        } else {
            $value = $this->getPayload('resource')->getValue($attribute_name);
        }

        $value = is_array($value) ? $value : [ $value ];

        if ($value === $this->attribute->getNullValue()) {
            return $default_value;
        } else {
            return $value;
        }
    }

    protected function getInputTemplateParameters()
    {
        $global_input_parameters = parent::getInputTemplateParameters();

        if (!empty($global_input_parameters['readonly'])) {
            $global_input_parameters['disabled'] = 'disabled';
        }

        return $global_input_parameters;
    }

    protected function getDefaultTranslationKeys()
    {
        $default_translation_keys = parent::getDefaultTranslationKeys();
        $html_attribute_translation_keys = [ 'add_images' ];

        return array_merge(
            $default_translation_keys,
            $html_attribute_translation_keys
        );
    }

    protected function getWidgetImplementor()
    {
        return 'jsb_Honeybee_Core/ui/ImageList';
    }
}
