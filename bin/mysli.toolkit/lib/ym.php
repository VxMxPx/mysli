<?php

namespace mysli\toolkit; class ym
{
    const __use = '
        .{
            type.str -> str,
            type.arr -> arr,
            fs.file  -> file,
            exception.ym
        }
    ';

    /**
     * Decode a particular .ym file and return an array.
     * --
     * @param string $filename
     * --
     * @throws mysli\toolkit\exception\ym N MESSAGE
     * --
     * @return array
     */
    static function decode_file($filename)
    {
        try
        {
            return static::decode(file::read($filename));
        }
        catch (exception\ym $e)
        {
            throw new exception\ym(
                $e->getMessage()."\nFile: {$filename}", $e->getCode()
            );
        }
    }

    /**
     * Decode .ym string and return an array.
     * --
     * @param string $string
     * --
     * @throws mysli\toolkit\exception\ym N MESSAGE
     * --
     * @return array
     */
    static function decode($string)
    {
        // Empty string
        if (!trim($string))
        {
            return [];
        }

        // So far converted string.
        $list = [];
        // Handle levels.
        $stack = [&$list];
        // Indentation type + size
        $indent = static::detect_indent($string);
        // Current indentation level.
        $level = 0;
        // To array
        $lines = str::lines($string);
        // Multiline
        // $type is QUOTE (""), INDENT (> )
        // $divider is either "\n" or ' ' (defines how new lines are divided).
        $multiline = [
            'enabled' => false,
            'buffer'  => '',
            'type'    => 'INDENT',
            'divider' => ' ',
            'level'   => 0,
            'key'     => null
        ];

        foreach ($lines as $lineno => $line)
        {
            // Get current indentation level
            $level = $indent ? static::get_level($line, $indent) : 0;

            // Buffering multiline text?
            if ($multiline['enabled'] === true)
            {
                if ($multiline['type'] === 'QUOTE')
                {
                    $multiline['buffer'] .= $multiline['divider'].trim($line);

                    if (substr($line, -1) === '"' && substr($line, -2) !== '\\"')
                    {
                        // End buffer
                        $stack[$multiline['level']][$multiline['key']] = rtrim($multiline['buffer'], '"');
                        $multiline['enabled'] = false;
                    }
                    continue;
                }
                elseif ($multiline['type'] === 'INDENT')
                {
                    if ($level === $multiline['level']+1)
                    {
                        $multiline['buffer'] .= $multiline['divider'].trim($line);
                        continue;
                    }
                    else
                    {
                        $stack[$multiline['level']][$multiline['key']] = ltrim($multiline['buffer']);
                        $multiline['enabled'] = false;
                    }
                }
            }

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

            $stack = array_slice($stack, 0, $level+1);

            try
            {
                // List item...
                if (substr(trim($line), 0, 1) === '-')
                {
                    list($_, $value) = static::proc_line(ltrim($line, "\t -"), true);

                    // just one - meaning sub category
                    if (!$value)
                    {
                        $key = count($stack[$level]);
                        $stack[$level][$key] = [];
                        $stack[] = &$stack[$level][$key];
                    }
                    else
                    {
                        $stack[$level][] = static::valufy($value);
                    }

                    continue;
                }

                list($key, $value) = static::proc_line($line, false);

                // Multiline?
                if (($value === '>' || $value === '|') ||
                    substr($value, 0, 1) === '"' && substr($value, -1) !== '"')
                {
                    // Define multiline
                    $multiline = [
                        'enabled' => true,
                        'buffer'  => trim($value, '">|'),
                        'type'    => (substr($value, 0, 1) !== '"' ? 'INDENT' : 'QUOTE'),
                        'divider' => $value === '|' ? "\n" : ' ',
                        'level'   => $level,
                        'key'     => $key
                    ];
                    continue;
                }
                elseif (trim($value) !== '')
                {
                    $value = static::valufy($value);
                    $stack[$level][$key] = $value;
                }
                else
                {
                    $stack[$level][$key] = [];
                    $stack[] = &$stack[$level][$key];
                }
            }
            catch (\Exception $e)
            {
                throw new exception\ym(
                    $e->getMessage()."\n".err_lines($lines, $lineno),
                    $e->getCode()
                );
            }
        }

        return $list;
    }

    /**
     * Encode an array to .ym file.
     * --
     * @param string $filename
     * @param array  $in
     * --
     * @throws mysli\toolkit\exception\ym N MESSAGE
     * --
     * @return boolean
     */
    static function encode_file($filename, array $in)
    {
        try
        {
            return file::write($filename, static::encode($in));
        }
        catch (exception\ym $e)
        {
            throw new exception\ym(
                $e->getMessage()."\nFile: {$filename}",
                $e->getCode()
            );
        }
    }

    /**
     * Encode an array to .ym string.
     * --
     * @param array   $in
     * @param integer $lvl
     *        Current indentation level (0).
     * --
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
                    $output .= "\n".static::encode($value, $lvl+1);
                }

                continue;
            }

            // Convert value
            if (is_numeric($value) && is_string($value))
                $value = '"'.$value.'"';
            elseif (in_array(strtolower($value), ['yes', 'true']))
                $value = '"Yes"';
            elseif (in_array(strtolower($value), ['no', 'false']))
                $value = '"No"';
            elseif (is_null($value))
                $value = 'null';
            elseif ($value === true)
                $value = 'Yes';
            elseif ($value === false)
                $value = 'No';

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

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Extract key / value from line!
     * --
     * @param string  $line
     *
     * @param boolean $li
     *        List item?
     * --
     * @throws mysli\toolkit\exception\ym 10 Missing colon.
     * @throws mysli\toolkit\exception\ym 20 Expected array to be closed.
     * --
     * @return array
     *         [ string $key, string $value ]
     */
    protected static function proc_line($line, $li)
    {
        if (!$li)
        {
            $segments = explode(':', trim($line), 2);
            $key   = null;
            $value = null;

            if (!isset($segments[1]))
            {
                throw new exception\ym("Missing colon (:).", 10);
            }
            else
            {
                $key = $segments[0];
                $value = $segments[1];
            }
            $key = trim($key,   "\t \"");
        }
        else
        {
            $key = null;
            $value = $line;
        }

        $value = trim($value, "\t ");

        return [$key, $value];
    }

    /**
     * Resolve inline array and return array.
     * --
     * @param  string $line
     * --
     * @throws mysli\toolkit\exception\ym  5 Unexpected character.
     * @throws mysli\toolkit\exception\ym  9 Unexpected colon, expecting value.
     * @throws mysli\toolkit\exception\ym 10 Unexpected colon.
     * @throws mysli\toolkit\exception\ym 20 Unclosed array.
     * --
     * @return array
     */
    protected static function resolve_array($line)
    {
        // $line = trim($line, " \t[]");

        // Weather next character should be escaped \\
        $escaped = false;

        // Weather is is envelope ""
        $envelope = false;

        // Array key
        $key = null;

        // Current buffer
        $buffer = null;

        // Weather next item is needed
        // (in such case only acceptable characters are: ` `, `]`, `,`, EOL)
        $need_next = false;

        $collection = [];

        // Current array stack...
        $pocket = [ &$collection ];

        $characters = preg_split("//u", $line, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($characters as $cpos => $char)
        {
            // Properly handle closed lists, proceeding with no comma...
            if ($need_next)
            {
                if ($char == ',' || $char == ']')
                    $need_next = false;
                elseif ($char != ' ')
                    throw new exception\ym(
                        f_error(
                            $line, $cpos,
                            "Unexpected character, expecting: `]` or `,`"
                        ), 5
                    );
            }

            // Handle escaped or enveloped \ or "
            if ( ($escaped || $envelope) && ($char !== '"' && $char !== '\\'))
            {
                $buffer .= $char;
                continue;
            }

            // Handle special characters or add character to the list...
            switch ($char)
            {
                /*
                ESCAPE
                 */
                case '\\':
                    if ($escaped)
                    {
                        $buffer .= '\\';
                        $escaped = false;
                    }
                    else
                        $escaped = true;
                break;

                /*
                Buffer start
                 */
                case '"':
                    $buffer .= '"';

                    if ($escaped)
                    {
                        $escaped = false;
                        break;
                    }

                    $envelope = !$envelope;
                break;

                /*
                Key/value separator
                 */
                case ':':
                    if ($key !== null)
                    {
                        throw new exception\ym(
                            f_error($line, $cpos, "Unexpected colon, expecting value..."), 9
                        );
                    }

                    // Key : Value
                    if (!empty(trim($buffer)))
                    {
                        $key = trim( trim($buffer), '"' );
                        $buffer = null;
                    }
                    else
                    {
                        throw new exception\ym(
                            f_error($line, $cpos, "Unexpected colon..."), 10
                        );
                    }
                break;

                /*
                Comma
                 */
                case ',':
                    $buffer = trim($buffer);
                    if (!empty(trim($buffer, '"')))
                    {
                        $buffer = static::valufy($buffer);
                        if ($key !== null)
                            $pocket[count($pocket)-1][$key] = $buffer;
                        else
                            $pocket[count($pocket)-1][] = $buffer;
                    }

                    $buffer = $key = null;
                break;

                /*
                Sub Array, Open
                 */
                case '[':
                    $array = [];

                    if ($key !== null)
                        $pocket[count($pocket)-1][$key] = &$array;
                    else
                        $pocket[count($pocket)-1][] = &$array;

                    $buffer = $key = null;
                    $pocket[] = &$array;
                    unset($array);
                break;

                /*
                Sub Array, Close
                 */
                case ']':
                    $buffer = trim($buffer);
                    if (!empty(trim($buffer, '"')))
                    {
                        $buffer = static::valufy($buffer);
                        if ($key !== null)
                            $pocket[count($pocket)-1][$key] = $buffer;
                        else
                            $pocket[count($pocket)-1][] = $buffer;
                    }

                    $need_next = true;
                    $key = $buffer = null;
                    array_pop($pocket);
                break;

                /*
                Append!
                 */
                default:
                    $buffer .= $char;
                break;
            }
        }

        // Check of errors...
        if (count($pocket) > 1)
        {
            throw new exception\ym("Unclosed array.", 20);
        }

        $buffer = $key = null;
        return $collection[0];
    }

    /**
     * Convert string representation of value to correct type.
     * --
     * @param  string $value
     * --
     * @return mixed
     */
    protected static function valufy($value)
    {
        if (empty($value)) return '';

        if (is_numeric($value))
        {
            return strpos($value, '.')
                ? (float) $value
                : (int) $value;
        }
        elseif ($value === '[]')
        {
            return [];
        }
        elseif (substr($value, 0, 1) === '[')
        {
            if (substr($value, -1) !== ']')
                throw new exception\ym("Expected array to be closed.", 20);

            // Resolve array
            return static::resolve_array($value);
        }
        elseif (in_array(strtolower($value), ['yes', 'true']))
        {
            return true;
        }
        elseif (in_array(strtolower($value), ['no', 'false']))
        {
            return false;
        }
        elseif (strtolower($value) === 'null')
        {
            return null;
        }
        else
        {
            return static::unquote($value);
        }
    }

    /**
     * Unquoate value in a smart way (if value starts and ends in same '' or "")
     * then remove first and last character.
     * --
     * @param string $value
     * -`-
     * @return string
     */
    protected static function unquote($value)
    {
        if (!$value)
        {
            return;
        }

        $start = substr($value, 0, 1);

        if (!in_array($start, ['"', "'"]))
        {
            return $value;
        }

        if ($start !== substr($value, -1))
        {
            return $value;
        }

        return substr($value, 1, -1);
    }

    /**
     * Get current indentation level.
     * --
     * @param string $line
     * @param string $indent
     * --
     * @return integer
     */
    protected static function get_level($line, $indent)
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
     * Get Indentation (type).
     * --
     * @param string $string
     * --
     * @return string
     */
    protected static function detect_indent($string)
    {
        if (preg_match('/(^[ \t]+)/m', $string, $matches))
        {
            return $matches[1];
        }
    }
}
