<?php

namespace Honeybee\Infrastructure\Command;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Ramsey\Uuid\Uuid;
use Trellis\Common\BaseObject;

abstract class Command extends BaseObject implements CommandInterface
{
    protected $uuid;

    protected $metadata;

    public function __construct(array $state = [])
    {
        $this->metadata = [];
        $this->uuid = Uuid::uuid4()->toString();

        parent::__construct($state);

        $this->guardRequiredState();
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function withMetadata(Metadata $metadata)
    {
        return $this->createCopyWith([ 'metadata' => $metadata->toArray() ]);
    }

    public function __toString()
    {
        return static::CLASS . '@' . $this->uuid;
    }

    public static function getType()
    {
        $fqcn_parts = explode('\\', static::CLASS);
        if (count($fqcn_parts) < 4) {
            throw new RuntimeError(
                sprintf(
                    'A concrete command class must be made up of at least four namespace parts: ' .
                    '(vendor, package, type, command), in order to support auto-type generation.' .
                    ' The given command-class %s only has %d parts.',
                    static::CLASS,
                    count($fqcn_parts)
                )
            );
        }
        $vendor = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $package = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $type = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $command = str_replace('_command', '', StringToolkit::asSnakeCase(array_pop($fqcn_parts)));

        return sprintf('%s.%s.%s.%s', $vendor, $package, $type, $command);
    }

    protected function guardRequiredState()
    {
        Assertion::uuid($this->uuid);
        Assertion::isArray($this->metadata);
    }
}
