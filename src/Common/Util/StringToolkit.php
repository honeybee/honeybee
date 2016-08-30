<?php

namespace Honeybee\Common\Util;

use Trellis\Runtime\Entity\EntityInterface;
use DateTimeInterface;
use Exception;
use Honeybee\Common\ScopeKeyInterface;

/**
 * Class with various useful methods handling or converting strings.
 */
class StringToolkit
{
    public static function asStudlyCaps($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = ucwords(str_replace(array('_', '-'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    public static function asCamelCase($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return lcfirst(self::asStudlyCaps($value));
    }

    public static function asSnakeCase($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return ctype_lower($value) ? $value : mb_strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $value));
    }

    public static function endsWith($haystack, $needle)
    {
        $needles = (array)$needle;

        foreach ($needles as $needle) {
            $length = mb_strlen($needle);

            if ($length == 0 || mb_substr($haystack, -$length, $length) === $needle) {
                return true;
            }
        }

        return false;
    }

    public static function startsWith($haystack, $needle)
    {
        $needles = (array)$needle;

        foreach ($needles as $needle) {
            $length = mb_strlen($needle);
            if ($length == 0 || mb_substr($haystack, 0, $length) === $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formats bytes into a human readable string.
     *
     * @param int $bytes
     *
     * @return string
     */
    public static function formatBytes($bytes)
    {
        $bytes = (int) $bytes;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 3) . ' ' . $units[$i];
    }

    /**
     * Escape given string for HTML contexts.
     *
     * @param string $string input string that should be output in html contexts
     *
     * @return string htmlspecialchars encoded string
     */
    public static function escapeHtml($string)
    {
        if (!defined('ENT_SUBSTITUTE')) {
            define('ENT_SUBSTITUTE', 8);
        }

        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Returns a string representation for the given argument. Specifically
     * handles known scalars or types like exceptions and entities.
     *
     * @param mixed $var object, array or string to create textual representation for
     *
     * @return string for the given argument
     */
    public static function getAsString($var)
    {
        if ($var instanceof Exception) {
            return (string)$var;
        } elseif (is_object($var)) {
            return self::getObjectAsString($var);
        } elseif (is_array($var)) {
            return print_r($var, true);
        } elseif (is_resource($var)) {
            return (string)sprintf('resource(type=%s)', get_resource_type($var));
        } elseif (true === $var) {
            return 'true';
        } elseif (false === $var) {
            return 'false';
        } elseif (null === $var) {
            return 'null';
        }

        return (string)$var;
    }

    /**
     * Returns a string for the given object enhanced by various information if
     * the object is of a known type. The given object should implement a
     * `__toString()` method as otherwise the representation might be empty.
     *
     * @param mixed $obj object to create a string for
     *
     * @return string with simple object representation
     */
    public static function getObjectAsString($obj)
    {
        // @todo we could introduce an argument to enhance to representation with type information or similar
        if ($obj instanceof EntityInterface) {
            return (string)$obj->getIdentifier();
        } elseif ($obj instanceof ScopeKeyInterface) {
            return (string)$obj->getScopeKey();
        } elseif ($obj instanceof DateTimeInterface) {
            return $obj->format('c');
        } elseif (is_callable(array($obj, '__toString'))) {
            return $obj->__toString();
        } else {
            return json_encode($obj);
        }
    }

    public static function generateRandomToken()
    {
        return sha1(
            sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            )
        );
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = str_replace(
            array('Ä', 'ä', 'Ö', 'ö', 'Ü', 'ü', 'ß'),
            array('Ae', 'ae', 'Oe', 'oe', 'Ue', 'ue', 'ss'),
            $text
        );
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
