<?php

namespace mysli\framework\ym;

__use(__namespace__, '
    mysli/framework/type/str
    mysli/framework/fs/file
    mysli/framework/exception/{...} AS framework/exception/{...}
');

class ym {
    /**
     * Decode particular file.
     * @param  string $filename
     * @return array
     */
    static function decode_file($filename) {
        return self::decode(file::read($filename));
    }
    /**
     * Decode .ym string.
     * @param  string $string
     * @return array
     */
    static function decode($string) {
        // Empty string
        if (!trim($string)) {
            return [];
        }
        $list = [];
        $stack = [&$list];
        $indent = self::detect_indent($string);
        $level  = 0;
        $string = str::to_unix_line_endings($string);
        $cl = 0;
        foreach (explode("\n", $string) as $line) {
            // Current line +`
            $cl++;
            // An empty line
            if (!trim($line)) { continue; }
            // Comment
            if (substr(trim($line), 0, 1) === '#') { continue; }
            // Get current indentation level
            $level = $indent ? self::get_level($line, $indent) : 0;

            $stack = array_slice($stack, 0, $level+1);
            // List item...
            if (substr(trim($line), 0, 1) === '-') {
                list($key, $value) = self::proc_line(
                    ltrim($line, "\t -"), true, $cl);
                // just one - meaning sub category
                if (!($key.$value)) {
                    $key = count($stack[$level]);
                    $stack[$level][$key] = [];
                    $stack[] = &$stack[$level][$key];
                } else {
                    $key
                        ? $stack[$level][$key] = $value
                        : $stack[$level][] = $value;
                }
                continue;
            }

            list($key, $value) = self::proc_line($line, false, $cl);
            if ($value === null) {
                $stack[$level][$key] = [];
                $stack[] = &$stack[$level][$key];
            } else {
                $stack[$level][$key] = $value;
            }
        }
        return $list;
    }
    /**
     * Extract key / value from line!
     * @param  string  $line
     * @param  boolean $li   list item?
     * @param  integer $cl   current line
     * @return array
     */
    private static function proc_line($line, $li, $cl) {
        $segments = explode(':', trim($line), 2);
        $key   = null;
        $value = null;
        if (!isset($segments[1])) {
            if (!$li) {
                throw new framework\exception\data(
                    "Error unexpected value: `{$line}` on line: ".
                    "`{$cl}`. Colon (:) is required.", 1);
            } else {
                $value = $segments[0];
            }
        } else {
            $key = $segments[0];
            $value = $segments[1];
        }

        $key   = trim($key,   "\t \"");
        $value = trim($value, "\t ");

        if ($value) {
            if (is_numeric($value)) {
                $value = strpos($value, '.')
                    ? (float) $value : (int) $value;
            } elseif (in_array(strtolower($value), ['yes', 'true'])) {
                $value = true;
            } elseif (in_array(strtolower($value), ['no', 'false'])) {
                $value = false;
            } else {
                $value = trim($value, '"');
            }
        } else {
            $value = null;
        }

        return [$key, $value];
    }
    /**
     * Get current indentation level.
     * @param  string $line
     * @param  string $indent
     * @return integer
     */
    private static function get_level($line, $indent) {
        $level = 0;
        $indent_length = strlen($indent);
        while (substr($line, 0, $indent_length) === $indent) {
            $line = substr($line, $indent_length);
            $level++;
        }
        return $level;
    }
    /**
     * Get Indentation (type)
     * @param  string $string
     * @return mixed
     */
    private static function detect_indent($string) {
        if (preg_match('/(^[ \t]+)/m', $string, $matches)) {
            return $matches[1];
        }
    }
}
