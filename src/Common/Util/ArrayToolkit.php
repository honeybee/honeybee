<?php

namespace Honeybee\Common\Util;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class with various useful methods handling or merging arrays.
 */
class ArrayToolkit
{
    /**
     * Tells if a given array is associative or not.
     *
     * @return bool
     */
    public static function isAssoc($array)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Converts a given multi-dimensional assoc array into a one-dimensional assoc array.
     * Nested keys are represented as an array path e.g. [ 'foo' => [ 'bar' => 42 ] ] becomes [ 'foo.bar' => 42 ]
     *
     * @param array $array
     * @param string $parent_prefix
     *
     * @return array
     */
    public static function flatten(array $array, $parent_prefix = '')
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            $key = $parent_prefix . $key;
            if (is_array($value) && self::isAssoc($value)) {
                $flattened = array_merge(self::flatten($value, $key . '.'), $flattened);
            } else {
                $flattened[$key] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Merges the given second array over the first one similar to the PHP internal
     * array_merge_recursive method, but does not change scalar values into arrays
     * when duplicate keys occur.
     *
     * @param array $first first or default array
     * @param array $second array to merge over the first array
     *
     * @return array merged result with scalar values still being scalar
     */
    public static function mergeScalarSafe(array &$first, array &$second)
    {
        $merged = $first;

        foreach ($second as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::mergeScalarSafe($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Moves an item to the top of the array. Works for numeric and string keys.
     *
     * @param array $array array to modify inplace
     * @param mixed $key key of array item to move
     *
     * @return void
     */
    public static function moveToTop(array &$array, $key)
    {
        $temp = [
            $key => $array[$key]
        ];
        unset($array[$key]);
        $array = $temp + $array;
    }

    /**
     * Moves an item to the bottom of the array. Works for numeric and string keys.
     *
     * @param array $array array to modify inplace
     * @param mixed $key key of array item to move
     *
     * @return void
     */
    public static function moveToBottom(array &$array, $key)
    {
        $value = $array[$key];
        unset($array[$key]);
        $array[$key] = $value;
    }

    /**
     * Returns a flat associative array with the query parameters of the given URL.
     * The array may be used to render hidden input elements on GET forms to not
     * lose query params when submitting a GET form (POST forms are working fine).
     *
     * Example: http://some.tld?limit=2&foo[0]=1&foo[1]=2&foo[2]=3 will be:
     *          [ "limit" => 2, "foo[0]" => 1, "foo[1]" => 2, "foo[2]" => 3 ]
     *
     * @param string $url URL with query parameters
     *
     * @return array of form parameters (key => value)
     */
    public static function getUrlQueryInRequestFormat($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);

        $query_params = [];
        parse_str($query, $query_params);

        return static::flattenToRequestFormat($query_params);
    }

    public static function flattenToRequestFormat(array $query_params)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($query_params),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $keys = [];
        $flat_array = [];

        foreach ($iterator as $key => $value) {
            $keys[$iterator->getDepth()] = $key;
            $name = $keys[0];

            if ($iterator->getDepth() > 0) {
                $name .= sprintf(
                    '[%s]',
                    implode('][', array_slice($keys, 1, $iterator->getDepth()))
                );
            }

            if (!is_array($value)) {
                $flat_array[$name] = $value;
            }
        }

        return $flat_array;
    }

    public static function flattenToArrayPath(array $array_keys)
    {
        $parts = $array_keys;
        if (count($parts) == 0) {
            return '';
        }

        $name = $parts[0];
        $parts = array_slice($parts, 1);

        $path = '';
        if (count($parts)) {
            $path = sprintf('[%s]', implode('][', $parts));
        }

        return $name . $path;
    }

    public static function filterEmptyValues(array $array, callable $callback = null, $recursive = true)
    {
        $filtered = [];

        foreach ($array as $prop => $value) {
            if (is_array($value) && $recursive) {
                $value = self::filterEmptyValues($value, $callback, $recursive);
            }

            if (true === (is_callable($callback) ? call_user_func($callback, $value) : !empty($value))) {
                $filtered[$prop] = $value;
            }
        }

        return $filtered;
    }

    /**
     * @param array $needles values to search for
     * @param array $haystack values to search through
     *
     * @return bool true if ANY of the needles exists in the haystack
     */
    public static function anyInArray(array $needles, array $haystack)
    {
        return !!array_intersect($needles, $haystack);
    }

    /**
     * @param array $needles values to search for
     * @param array $haystack values to search through
     *
     * @return bool true if ALL of the needles exist in the haystack
     */
    public static function allInArray(array $needles, array $haystack)
    {
        return !array_diff($needles, $haystack);
    }
}
