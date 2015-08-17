<?php

namespace Honeybee\Infrastructure\Config;

use JmesPath\Env as JmesPath;
use ArrayObject;
use JsonSerializable;
use RuntimeException;

class Settings extends ArrayObject implements SettingsInterface, JsonSerializable
{
    /**
     * @var string default iterator used
     */
    protected $iterator_class = 'ArrayIterator';

    /**
     * @var bool whether changing data is allowed or not
     */
    protected $allow_modification = false;

    /**
     * Create a new instance with the given data as initial value set.
     *
     * @param array $data initial options
     * @param string $iterator_class implementor to use for iterator
     */
    public function __construct(array $data = array(), $iterator_class = 'ArrayIterator')
    {
        if (!empty($iterator_class)) {
            $this->iterator_class = trim($iterator_class);
        }

        $this->allow_modification = true;

        parent::__construct(array(), self::ARRAY_AS_PROPS, $this->iterator_class);

        foreach ($data as $key => $value) {
            $this->offsetSet($key, $value);
        }

        $this->allow_modification = false;
    }

    /**
     * Returns a new Settings instance hydrated with the given initial options.
     *
     * @param array $settings Initial options.
     *
     * @return Settings
     */
    public static function createFromArray(array $settings = array())
    {
        return new static($settings);
    }

    /**
     * Returns whether the key exists or not.
     *
     * @param mixed $key name of key to check
     *
     * @return bool true, if key exists; false otherwise
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Returns the value for the given key.
     *
     * @param mixed $key name of key
     * @param mixed $default value to return if key doesn't exist
     *
     * @return mixed value for that key or default given
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return $default;
    }

    /**
     * Returns all first level key names.
     *
     * @return array of keys
     */
    public function getKeys()
    {
        return array_keys((array)$this);
    }

    /**
     * Allows to search for specific data values via JMESPath expressions.
     *
     * Some example expressions as a quick start:
     *
     * - "nested.key"           returns the value of the nested "key"
     * - "nested.*"             returns all values available under the "nested" key
     * - "*.key"                returns all values of "key"s on any second level array
     * - "[key, nested.key]"    returns first level "key" value and the first value of the "nested" key array
     * - "[key, nested[0]"      returns first level "key" value and the first value of the "nested" key array
     * - "nested.key || key"    returns the value of the first matching expression
     *
     * @see http://jmespath.readthedocs.org/en/latest/ and https://github.com/mtdowling/jmespath.php
     *
     * @param string $expression JMESPath expression to evaluate on stored data
     *
     * @return mixed|null data in various types (scalar, array etc.) depending on the found results
     *
     * @throws \JmesPath\SyntaxErrorException on invalid expression syntax
     * @throws \RuntimeException e.g. if JMESPath cache directory cannot be written
     * @throws \InvalidArgumentException e.g. if JMESPath builtin functions can't be called
     */
    public function getValues($expression = '*')
    {
        return JmesPath::search($expression, $this->toArray());
    }

    /**
     * Returns the data as an associative array.
     *
     * @return array with all data
     *
     * @throws BadValueException when no toArray method is available on an object value
     */
    public function toArray()
    {
        $data = array();

        foreach ($this as $key => $value) {
            if (is_object($value)) {
                if (!is_callable(array($value, 'toArray'))) {
                    throw new RuntimeException('Object does not implement toArray() method on key: ' . $key);
                }
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }


    //
    // overridden ArrayObject methods (to prevent modification if necessary)
    //


    /**
     * Returns the value of the specified key.
     *
     * @param mixed $key name of key to get
     *
     * @return mixed|null value or null if non-existant key
     */
    public function offsetGet($key)
    {
        if (!$this->offsetExists($key)) {
            return null;
        }

        return parent::offsetGet($key);
    }

    /**
     * Sets the given data on the specified key.
     *
     * @param mixed $key name of key to set
     * @param mixed $data data to set for the given key
     *
     * @return void
     *
     * @throws RuntimeException on write attempt when modification is forbidden
     */
    public function offsetSet($key, $data)
    {
        if (!$this->allow_modification) {
            throw new RuntimeException('Attempting to write to an immutable array');
        }

        if (isset($data) && is_array($data)) {
            $data = new static($data);
        }

        return parent::offsetSet($key, $data);
    }

    /**
     * Creates a copy of the data as an array.
     */
    public function getArrayCopy()
    {
        return $this->toArray();
    }

    /**
     * Appends the given new value as the last element to the internal data.
     *
     * @param $value value to append
     *
     * @return void
     *
     * @throws RuntimeException on write attempt when modification is forbidden
     */
    public function append($value)
    {
        if (!$this->allow_modification) {
            throw new RuntimeException('Attempting to append to an immutable array');
        }

        return parent::append($value);
    }

    /**
     * Exchanges the current data array with another array.
     *
     * @param array $data array with key-value pairs to set as new data
     *
     * @return array old data
     *
     * @throws RuntimeException on write attempt when modification is forbidden
     */
    public function exchangeArray($data)
    {
        if (!$this->allow_modification) {
            throw new RuntimeException('Attempting to exchange data of an immutable array');
        }

        return parent::exchangeArray($data);
    }

    /**
     * Unsets the value of the given key.
     *
     * @param mixed key key to remove
     *
     * @return void
     *
     * @throws RuntimeException on write attempt when modification is forbidden
     */
    public function offsetUnset($key)
    {
        if (!$this->allow_modification) {
            throw new RuntimeException('Attempting to unset key on an immutable array');
        }

        if ($this->offsetExists($key)) {
            parent::offsetUnset($key);
        }
    }

    /**
     * @return array data which can be serialized by json_encode
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /* todo do we need serialize/unserialize support because of the nesting?
    public function serialize() {
        return serialize($this->data);
    }
    public function unserialize($data) {
        $this->data = unserialize($data);
    }*/

    /**
     * Enables deep clones.
     */
    public function __clone()
    {
        foreach ($this as $key => $value) {
            if (is_object($value)) {
                $this[$key] = clone $value;
            }
        }
    }

    /**
     * @return string simple representation of the internal array
     */
    public function __toString()
    {
        return (string)var_export($this->toArray(), true);
    }
}
