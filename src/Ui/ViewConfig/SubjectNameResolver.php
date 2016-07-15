<?php

namespace Honeybee\Ui\ViewConfig;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Entity;
use Honeybee\Projection\Projection;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Ui\Activity\ActivityInterface;
use ReflectionClass;

class SubjectNameResolver implements NameResolverInterface
{
    /**
     * Resolves a name for the given subject. That name may be used to
     * look up e.g. renderer configs or similar for that subject.
     *
     * @example some Some\Foo\ProjectionList => 'resource_collection'
     * @example some specific Honeybee\Entity => 'vendor.module.type_prefix'
     * @example some specific Honeybee\Ui\Activity\ActivityInterface => 'resource_collection.activity'
     *
     * @param mixed $subject subject to resolve a name for
     *
     * @return string name for that subject
     */
    public function resolve($subject)
    {
        if (empty($subject)) {
            throw new RuntimeError(
                'A subject must be given to resolve a name from it. A simple string will be returned unaltered.'
            );
        }

        if (is_string($subject)) {
            return $subject;
        }

        $renderer_config_name = null;
        if ($subject instanceof Entity) {
            $renderer_config_name = $subject->getType()->getScopeKey();
            if ($subject instanceof Projection) {
                $variant = $subject->getType()->getVariant();
                if ($variant !== ProjectionTypeInterface::DEFAULT_VARIANT) {
                    $renderer_config_name .= '.' . StringToolkit::asSnakeCase($variant);
                }
            }
        } else {
            $subject_name = gettype($subject);
            if (is_object($subject)) {
                $subject_class = new ReflectionClass($subject);
                $subject_name = $subject_class->getShortName();
                if ($subject instanceof ActivityInterface) {
                    $subject_name = $subject->getName() . $subject_name;
                }
            }

            $renderer_config_name = $this->getAsSnakeCase($subject_name);
        }

        return $renderer_config_name;
    }

    protected function getAsSnakeCase($string)
    {
        return StringToolkit::asSnakeCase($string);
    }
}
