<?php

namespace Honeybee\Infrastructure\Command;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Ramsey\Uuid\Uuid;

abstract class Command implements CommandInterface
{
    /**
     * @var string $uuid
     */
    protected $uuid;

    /**
     * @var mixed $metadata
     */
    protected $metadata;

    /**
     * @param mixed[] $state
     */
    public function __construct(array $state = [])
    {
        $this->metadata = [];
        $this->uuid = Uuid::uuid4()->toString();
        foreach ($state as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }

        $this->guardRequiredState();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return mixed[]
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param Metadata $metadata
     *
     * @return CommandInterface
     */
    public function withMetadata(Metadata $metadata)
    {
        return $this->createCopyWith([ 'metadata' => $metadata->toArray() ]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return static::CLASS . '@' . $this->uuid;
    }

    /**
     * @return string
     *
     * @throws RuntimeError
     */
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
        $vendor = strtolower(array_shift($fqcn_parts));
        $package = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $type = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $command = str_replace('_command', '', StringToolkit::asSnakeCase(array_pop($fqcn_parts)));

        return sprintf('%s.%s.%s.%s', $vendor, $package, $type, $command);
    }

    /**
     * @return mixed[]
     */
    public function toArray()
    {
       return [
            '@type' => static::CLASS,
           'uuid' => $this->uuid,
           'metadata' => $this->metadata
       ];
    }

    protected function guardRequiredState()
    {
        Assertion::uuid($this->uuid);
        Assertion::isArray($this->metadata);
    }
}
