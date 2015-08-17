<?php

namespace Honeybee\Common\Util;

use Honeybee\Common\Error\RuntimeError;

class PhpClassParser
{
    protected static $t_whitespace = [ T_ENCAPSED_AND_WHITESPACE, T_WHITESPACE ];

    protected $tokens;

    protected $pos;

    protected $saved_pos;

    public function parse($class_file_path)
    {
        if (!is_readable($class_file_path)) {
            throw new RuntimeError(sprintf("Unable to read given php class file at %s", $class_file_path));
        }

        $this->tokens = token_get_all(file_get_contents($class_file_path));
        $this->pos = 0;

        $this->save();
        $namespace = $this->parseNamespace();
        if (!$namespace) {
            $this->restore();
        }

        $this->save();
        $class_name = $this->parseClassName();
        if (!$class_name) {
            $this->restore();
        }

        return new ClassFileInfo(
            [
                'namespace' => $namespace,
                'class_name' => $class_name,
                'class_file_path' => $class_file_path
            ]
        );
    }

    protected function current()
    {
        return isset($this->tokens[$this->pos]) ? $this->tokens[$this->pos] : null;
    }

    protected function next()
    {
        $this->pos++;

        return $this->current();
    }

    protected function skip($token_or_tokens)
    {
        $skip_tokens = (array)$token_or_tokens;

        do {
            $next_token = $this->next();
            $next_token = is_array($next_token) ? $next_token[0] : $next_token;
        } while($next_token !== null && in_array($next_token, $skip_tokens));
    }

    protected function seek($token_or_tokens)
    {
        $expected_tokens = (array)$token_or_tokens;

        do {
            $next_token = $this->next();
            $next_token = is_array($next_token) ? $next_token[0] : $next_token;
        } while($next_token !== null && !in_array($next_token, $expected_tokens));
    }

    protected function parseNamespace()
    {
        $this->seek(T_NAMESPACE);
        $this->skip(self::$t_whitespace);
        $token = $this->current();
        $namespace = '';
        while ($token !== null && $token !== ';') {
            $namespace .= is_array($token) ? $token[1] : $token;
            $token = $this->next();
        }
        // skip the ';'
        $this->next();

        return trim($namespace);
    }

    protected function parseClassName()
    {
        $this->seek(T_CLASS);
        $this->skip(self::$t_whitespace);
        $token = $this->current();

        $class_name = '';
        while (is_array($token) && $token[0] === T_STRING) {
            $class_name .= $token[1];
            $token = $this->next();
        }

        return trim($class_name);
    }

    protected function save()
    {
        $this->saved_pos = $this->pos;
    }

    protected function restore()
    {
        $this->pos = $this->saved_pos;
    }
}
