<?php

namespace Honeybee\Common\Util;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Common\Error\ParseError;

/**
 * Class with various useful methods for dealing with json.
 */
class JsonToolkit
{
    const DEFAULT_DEPTH = 512;

    const DEFAULT_OPTIONS = 0;

    const DEFAULT_ASSOC = true;

    public static function parse($json_string, SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings();

        $parsed_data = json_decode(
            $json_string,
            $settings->get('assoc', self::DEFAULT_ASSOC),
            $settings->get('depth', self::DEFAULT_DEPTH),
            $settings->get('options', self::DEFAULT_OPTIONS)
        );

        $last_error = json_last_error();
        if ($last_error !== JSON_ERROR_NONE) {
            throw new ParseError("Failed to parse json. Reason: " . json_last_error_msg());
        }

        return $parsed_data;
    }
}
