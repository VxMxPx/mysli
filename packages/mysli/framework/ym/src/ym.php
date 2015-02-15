<?php

namespace mysli\framework\ym;

__use(__namespace__, '
    mysli.framework.type/str
    mysli.framework.fs/file
    mysli.framework.exception/*  as  framework\exception\*
');

class ym {
    /**
     * Decode particular file.
     * @param  string $filename
     * @return array
     */
    static function decode_file($filename) {
        try {
            return self::decode(file::read($filename));
        } catch (framework\exception\parser $e) {
            throw new framework\exception\parser(
                $e->getMessage()."\nFile: {$filename}");
        }
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
        $lines = explode("\n", $string);

        foreach ($lines as $lineno => $line) {
            // An empty line
            if (!trim($line)) { continue; }
            // Comment
            if (substr(trim($line), 0, 1) === '#') { continue; }
            // Get current indentation level
            $level = $indent ? self::get_level($line, $indent) : 0;

            $stack = array_slice($stack, 0, $level+1);
            try {
                // List item...
                if (substr(trim($line), 0, 1) === '-') {
                    list($key, $value) = self::proc_line(
                        ltrim($line, "\t -"), true);
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

                list($key, $value) = self::proc_line($line, false);
                if ($value === null) {
                    $stack[$level][$key] = [];
                    $stack[] = &$stack[$level][$key];
                } else {
                    $stack[$level][$key] = $value;
                }
            } catch (\Exception $e) {
                throw new framework\exception\parser(
                    $e->getMessage()."\n".self::err_lines($lines, $lineno));
            }
        }
        return $list;
    }
    /**
     * Extract key / value from line!
     * @param  string  $line
     * @param  boolean $li   list item?
     * @return array
     */
    private static function proc_line($line, $li) {
        $segments = explode(':', trim($line), 2);
        $key   = null;
        $value = null;
        if (!isset($segments[1])) {
            if (!$li) {
                throw new framework\exception\data(
                    "Missing colon (:) or dash (-).", 1);
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
    /**
     * Return -$padding, $current, +$padding lines for exceptions, e.g.:
     *   11. ::if true
     * >>12.     {username|non_existant_function}
     *   13. ::/if
     * @param  array   $lines
     * @param  integer $current
     * @param  integer $padding
     * @return string
     */
    private static function err_lines($lines, $current, $padding=3) {
        $start    = $current - $padding;
        $end      = $current + $padding;
        $result   = '';
        for ($position = $start; $position <= $end; $position++) {
            if (isset($lines[$position])) {
                if ($position === $current) {
                    $result .= ">>";
                } else {
                    $result .= "  ";
                }
                $result .= ($position+1).". {$lines[$position]}\n";
            }
        }
        return $result;
    }
}
