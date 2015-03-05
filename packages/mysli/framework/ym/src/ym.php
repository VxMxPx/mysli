<?php

namespace mysli\framework\ym;

__use(__namespace__, '
    mysli.framework.type/str
    mysli.framework.fs/file
    mysli.framework.exception/* -> framework\exception\*
');

class ym
{
    /**
     * Decode particular file.
     * @param  string $filename
     * @return array
     */
    static function decode_file($filename)
    {
        try
        {
            return self::decode(file::read($filename));
        }
        catch (framework\exception\parser $e)
        {
            throw new framework\exception\parser(
                $e->getMessage()."\nFile: {$filename}"
            );
        }
    }
    /**
     * Decode .ym string.
     * @param  string $string
     * @return array
     */
    static function decode($string)
    {
        // Empty string
        if (!trim($string))
        {
            return [];
        }

        $list   = [];
        $stack  = [&$list];
        $indent = self::detect_indent($string);
        $level  = 0;
        $string = str::to_unix_line_endings($string);
        $lines  = explode("\n", $string);

        foreach ($lines as $lineno => $line)
        {
            // An empty line
            if (!trim($line))
            {
                continue;
            }

            // Comment
            if (substr(trim($line), 0, 1) === '#')
            {
                continue;
            }

            // Get current indentation level
            $level = $indent ? self::get_level($line, $indent) : 0;
            $stack = array_slice($stack, 0, $level+1);

            try
            {
                // List item...
                if (substr(trim($line), 0, 1) === '-')
                {
                    list($key, $value) = self::proc_line(ltrim($line, "\t -"), true);

                    // just one - meaning sub category
                    if (!($key.$value))
                    {
                        $key = count($stack[$level]);
                        $stack[$level][$key] = [];
                        $stack[] = &$stack[$level][$key];
                    }
                    else
                    {
                        $key
                            ? $stack[$level][$key] = $value
                            : $stack[$level][]     = $value;
                    }

                    continue;
                }

                list($key, $value) = self::proc_line($line, false);

                if ($value === null)
                {
                    $stack[$level][$key] = [];
                    $stack[] = &$stack[$level][$key];
                }
                else
                {
                    $stack[$level][$key] = $value;
                }
            }
            catch (\Exception $e)
            {
                throw new framework\exception\parser(
                    $e->getMessage()."\n".self::err_lines($lines, $lineno)
                );
            }
        }

        return $list;
    }
    /**
     * Encode an array to .ym file
     * @param  string $filename
     * @param  array  $in
     * @return boolean
     */
    static function encode_file($filename, array $in)
    {
        try
        {
            return file::write($filename, self::encode($in));
        }
        catch (framework\exception\parser $e)
        {
            throw new framework\exception\parser(
                $e->getMessage()."\nFile: {$filename}"
            );
        }
    }
    /**
     * Encode an array to .ym string
     * @param  array   $in
     * @param  integer $lvl current indentation level (0)
     * @return string
     */
    static function encode(array $in, $lvl=0)
    {
        $output = '';

        foreach ($in as $key => $value)
        {
            $output .= str_repeat(' ', $lvl*4);

            if (is_array($value))
            {
                if (is_integer($key))
                {
                    $output .= "-";
                }
                else
                {
                    $output .= "{$key}:";
                }

                if (empty($value))
                {
                    $output .= " []\n";
                }
                else
                {
                    $output .= "\n".self::encode($value, $lvl+1);
                }

                continue;
            }

            // Convert value
            if     (is_numeric($value) && is_string($value)) $value = '"'.$value.'"';
            elseif (in_array(strtolower($value), ['yes', 'true'])) $value = '"Yes"';
            elseif (in_array(strtolower($value), ['no', 'false'])) $value = '"No"';
            elseif ($value === true)  $value = 'Yes';
            elseif ($value === false) $value = 'No';

            if (is_integer($key))
            {
                $output .= "- {$value}\n";
            }
            else
            {
                $output .= "{$key}: {$value}\n";
            }
        }

        return $output;
    }
    /**
     * Extract key / value from line!
     * @param  string  $line
     * @param  boolean $li   list item?
     * @return array
     */
    private static function proc_line($line, $li)
    {
        $segments = explode(':', trim($line), 2);
        $key   = null;
        $value = null;

        if (!isset($segments[1]))
        {
            if (!$li)
            {
                throw new framework\exception\data(
                    "Missing colon (:) or dash (-).", 1
                );
            }
            else
            {
                $value = $segments[0];
            }
        }
        else
        {
            $key = $segments[0];
            $value = $segments[1];
        }

        $key   = trim($key,   "\t \"");
        $value = trim($value, "\t ");

        if ($value)
        {
            if (is_numeric($value))
            {
                $value = strpos($value, '.')
                    ? (float) $value
                    : (int) $value;
            }
            elseif ($value === '[]')
            {
                $value = [];
            }
            elseif (in_array(strtolower($value), ['yes', 'true']))
            {
                $value = true;
            }
            elseif (in_array(strtolower($value), ['no', 'false']))
            {
                $value = false;
            }
            else
            {
                $value = trim($value, '"');
            }
        }
        else
        {
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
    private static function get_level($line, $indent)
    {
        $level = 0;
        $indent_length = strlen($indent);

        while (substr($line, 0, $indent_length) === $indent)
        {
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
    private static function detect_indent($string)
    {
        if (preg_match('/(^[ \t]+)/m', $string, $matches))
        {
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
    private static function err_lines($lines, $current, $padding=3)
    {
        $start    = $current - $padding;
        $end      = $current + $padding;
        $result   = '';

        for ($position = $start; $position <= $end; $position++)
        {
            if (isset($lines[$position]))
            {
                if ($position === $current)
                {
                    $result .= ">>";
                }
                else
                {
                    $result .= "  ";
                }

                $result .= ($position+1).". {$lines[$position]}\n";
            }
        }

        return $result;
    }
}
