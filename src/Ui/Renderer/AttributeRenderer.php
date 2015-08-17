<?php

namespace Honeybee\Ui\Renderer;

use DateTimeInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\ArrayToolkit;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\EntityInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;
use Honeybee\Ui\UrlGeneratorInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\AttributeValuePath;
use Trellis\Runtime\Attribute\Timestamp\TimestampAttribute;
use Trellis\Runtime\ValueHolder\ComplexValueInterface;

abstract class AttributeRenderer extends Renderer
{
    const STATIC_TRANSLATION_PATH = 'fields';

    protected $attribute;

    protected function validate()
    {
        if (!$this->getPayload('resource') instanceof EntityInterface) {
            throw new RuntimeError('Payload "resource" must implement: ' . EntityInterface::CLASS);
        }

        if (!$this->getPayload('attribute') instanceof AttributeInterface) {
            throw new RuntimeError(
                sprintf('Instance of "%s" necessary.', AttributeInterface::CLASS)
            );
        }

        $this->attribute = $this->getPayload('attribute');
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $attribute_name = $this->attribute->getName();
        $attribute_path = $this->attribute->getPath();

        $params['field_id'] = 'randomId-' . rand(); // @todo still random but nicer ids?
        $params['field_name'] = $attribute_name;
        $params['grouped_field_name'] = $this->getGroupedInputFieldName();
        $params['grouped_base_path'] = $this->getGroupedInputFieldName();
        $params['attribute_name'] = $attribute_name;
        $params['attribute_path'] = $attribute_path;
        $params['attribute_value'] = $this->determineAttributeValue($attribute_name);
        $params['attribute_value_is_null_value'] = is_null($params['attribute_value']);
        $params['is_embedded'] = $this->getOption('is_within_embed_template', false);

        return $params;
    }

    protected function getDefaultTranslationDomain()
    {
        return sprintf(
            '%s.%s',
            $this->attribute->getRootType()->getPrefix(),
            self::STATIC_TRANSLATION_PATH
        );
    }

    protected function getGroupedInputFieldName()
    {
        $entity_type = $this->attribute->getType();

        $group_parts = $this->getOption('group_parts', []);
        if ($group_parts instanceof SettingsInterface) {
            $group_parts = $group_parts->toArray();
        } else if (!is_array($group_parts)) {
            throw new RuntimeError(
                'Invalid value type given for "group_parts" option. Only arrays are supported here.'
            );
        }

        $value_path = $this->getOption('attribute_value_path');
        $field_specific_group_parts = explode('.', $this->attribute->getPath());
        if (!empty($value_path)) {
            $value_path_group_parts = explode('.', $value_path);
            $calc_index = 1;
            foreach ($value_path_group_parts as $actual_idx => $value_path_group_part) {
                if ($calc_index % 2 === 0) {
                    if (preg_match('/[\w\*]+\[(\d+)\]/', $value_path_group_part, $matches)) {
                        $group_parts[] = $matches[1];
                    } else {
                        throw new RuntimeError(
                            sprintf(
                                'Invalid attribute_value_path "%s" given to renderer "%s" for field "%s".' .
                                ' Missing expected embed index within path specification.',
                                $value_path,
                                static::CLASS,
                                $this->attribute->getPath()
                            )
                        );
                    }
                } else {
                    $group_parts[] = $value_path_group_part;
                }
                $calc_index++;
            }
        } else {
            if ($this->attribute->getType()->isRoot()) {
                $group_parts = array_merge($group_parts, explode('.', $this->attribute->getPath()));
            } else {
                $group_parts[] = $this->attribute->getName();
            }
        }

        return ArrayToolkit::flattenToArrayPath($group_parts);
    }

    protected function determineAttributeValue($attribute_name, $default_value = '')
    {
        $value = '';

        if ($this->hasOption('value')) {
            return $this->getOption('value', $default_value);
        }

        $expression = $this->getOption('expression');
        $value_path = $this->getOption('attribute_value_path');
        if (!empty($value_path)) {
            $value = AttributeValuePath::getAttributeValueByPath($this->getPayload('resource'), $value_path);
        } else {
            $value = $this->getPayload('resource')->getValue($attribute_name);
        }

        // @todo introduce nested rendering or smarter mechanisms or error message for resources and other known types?
        if (is_object($value)) {
            if ($value instanceof DateTimeInterface) {
                $value = $value->format(TimestampAttribute::FORMAT_ISO8601);
            } elseif (!$value instanceof ComplexValueInterface) {
                $value = sprintf(
                    'Attribute "%s" (type "%s") – value of type object (%s): %s',
                    $attribute_name,
                    get_class($this->attribute),
                    get_class($value),
                    StringToolkit::getObjectAsString($value)
                );
            } else {
                // it's a complex value so we should not convert the object to string or similar
                // the specific renderer for that attribute might want to use the actual object
            }
        } elseif (is_array($value)) {
            $value = sprintf(
                'Attribute "%s" – value of type array with keys: %s',
                $attribute_name,
                print_r(array_keys($value), true)
            );
        } else {
            // $value = StringToolkit::getAsString($value);
        }

        if ($value === $this->attribute->getNullValue()) {
            return $default_value;
        } else {
            return $value;
        }
    }

    protected function evaluateExpression($expression)
    {
        $expression_params = [ 'resource' => $this->getPayload('resource') ];

        return $this->expression_service->evaluate($expression, $expression_params);
    }

     /**
     * Attributes can have different translation-keys depending on the current state of the root
     * resource.
     * To provide translation just for one specific state define a translation key with the state
     * appended after the translation key name:
     *
     *      e.g. translation key 'input_help' can have a specific translation when the resource is 'inactive',
     *      and that can be defined with a translation key 'input_help.inactive' in the translations.xml
     *
     * Check the Workflow.xml of the interested Resource for a list of available states.
     * If no 'per-state' translation is defined then the general translation key will be used as fallback:
     *
     *      e.g. 'input_help' if 'input_help.inactive' has not been defined
     *
     * If neither the fallback exists than the translation will not be included.
     *
     * @return array Translated strings to use in the template
     */
    protected function getTranslations($translation_domain = null)
    {
        $translation_keys = $this->getTranslationKeys($translation_domain);
        $translations = [];

        $resource_current_state = $this->getPayload('resource') instanceof ProjectionInterface
            ? $this->getPayload('resource')->getWorkflowState()
            : $this->getPayload('resource')->getRoot()->getWorkflowState();

        foreach ($translation_keys as $index => $key) {
            $translation_key = sprintf('%s.%s', $key, $resource_current_state);
            $translation = $this->_($translation_key, $translation_domain, null, null, '');

            // if a translation doesn't exist for the current state fallback to the stateless translation
            if (empty($translation)) {
                $translation_key = $key;
                $translation = $this->_($translation_key, $translation_domain, null, null, '');
            }
            // add just keys having a corresponding translations
            if (!empty($translation)) {
                $translations[$index] = $translation;
            }
        }
        return $translations;
    }

    protected function getDefaultTranslationKeys()
    {
        $default_translation_keys = parent::getDefaultTranslationKeys();
        $attribute_translation_keys = [ 'input_help', 'input_hint', 'input_focus_hint' ];

        return array_replace(
            $default_translation_keys,
            $attribute_translation_keys
        );
    }

    protected function isReadonly()
    {
        return (bool)($this->attribute->getOption('mirrored', false) || $this->getOption('readonly', false));
    }

    protected function isRequired()
    {
        return (bool)($this->getOption('required', $this->attribute->getOption('mandatory', false)));
    }
}
