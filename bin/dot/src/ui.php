<?php

/**
 * # Ui
 *
 * Use this class to achieve consistent CLI outputs.
 *
 * Commands, the exception is `template`, will not output string automatically,
 * but will rather return formated string.
 */
namespace dot; class ui
{
    /**
     * Ui template, allows you to fine-tune your CLI output.
     * --
     * @example
     * Template:
     * ```
     * $template = <<<TEMPLATE
     * <title>Hello {name}<title>
     * Available commands:
     * <ul>{commands}</ul>
     * TEMPLATE;
     * ```
     *
     * Then use it like this:
     * ```
     * ui::template(
     *     $template,
     *     [
     *         'name' => 'Zitalik',
     *         'commands' => [
     *             'One', 'Two', 'Three'
     *         ]
     *     ]
     * );
     * ```
     *
     * Result:
     * ```
     * Hello Zitalik
     *
     *    - One
     *    - Two
     *    - Three
     * ```
     * --
     * @param  string $template
     * @param  array  $variables use associative array, and in template {varname}
     */
    static function t($template, array $variables=[])
    {
        /*
        Find tags and send them methods
         */
        $template = preg_replace_callback(
            '/<([a-z]+)>(.*?)<\/\1>/s',
            function ($match) use ($variables)
            {
                $tag = $match[1];
                $text = $match[2];
                $trimed = trim($text);

                /*
                If we're dealing with a plain variable, replace it right now.
                 */
                if (substr($trimed, 0, 1) === '{' && substr($trimed, -1) === '}')
                {
                    $trimed = substr($trimed, 1, -1);
                    $text = isset($variables[$trimed]) ? $variables[$trimed] : $text;
                }

                /*
                Lists requires special logic
                 */
                if (in_array($tag, ['ul', 'ol', 'al']))
                {
                    if (!is_array($text))
                    {
                        $lines = explode("\n", $text);

                        if ($tag === 'al')
                        {
                            $nlines = [];

                            foreach ($lines as $line)
                            {
                                list($k, $v) = explode(':', $line, 2);
                                $nlines[trim($k)] = trim($v);
                            }
                        }
                        else
                        {
                            foreach ($lines as &$line)
                            {
                                $line = trim($line);
                            }
                            unset($line);
                        }

                        $text = $lines;
                    }
                }

                if (method_exists('dot\ui', $tag))
                {
                    return call_user_func(['dot\ui', $tag], $text, true);
                }
                else
                {
                    return is_array($text) ? implode("\n", $text) : $text;
                }
            },
            $template
        );

        /*
        Set variables
         */
        foreach ($variables as $key => $var)
        {
            if (is_array($var))
                $var = implode("\n", $var);

            $template = str_replace("{\$key}", $var, $template);
        }

        output::line($template);
    }

    /*
    --- Elements ---------------------------------------------------------------
     */

    /**
     * Used for titles.
     * --
     * @param string $string
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function title($string, $return=false)
    {
        $f = "\e[1m{$string}\e[21m";
        if ($return) return $f; else output::line($f);
    }

    /**
     * Unordered list.
     * --
     * @example
     * array `['One', 'Two', 'Three']` or when using format:
     * ```
     * <ul>
     * One
     * Two
     * Three
     * </ul>
     * ```
     * Result:
     * ```
     *    - One
     *    - Two
     *    - Three
     * ```
     * --
     * @param array $lines
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function ul(array $lines, $return=false)
    {
        $f = "";

        foreach ($lines as $line)
        {
            $f .= "\n   - {$line}";
        }

        if ($return) return $f; else output::line($f);
    }

    /**
     * Ordered list.
     * --
     * @example
     * array `['One', 'Two', 'Three']` or when using format:
     * ```
     * <ol>
     * One
     * Two
     * Three
     * </ol>
     * ```
     * Result:
     * ```
     *    1. One
     *    2. Two
     *    3. Three
     * ```
     * --
     * @param array $lines
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function ol(array $lines, $return=false)
    {
        $f = "";

        foreach ($lines as $k => $line)
        {
            $k = $k + 1;
            $f .= "\n   {$k}. {$line}";
        }

        if ($return) return $f; else output::line($f);
    }

    /**
     * Aligned list.
     * --
     * @example
     * array `['Name' => 'Zitalik', 'Age' => 2, 'City' => 'Maribor']` or
     * when using format:
     * ```
     * <al>
     * Name: Zitalik
     * Age: 2
     * City: Maribor
     * </al>
     * ```
     * Result:
     * ```
     *    Name: Zitalik
     *     Age: 2
     *    City: Maribor
     * ```
     * --
     * @param array $lines
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function al(array $lines, $return=false)
    {
        $f = "";
        $longest = 0;

        foreach ($lines as $k => $v)
        {
            if (mb_strlen($k) > $longest)
                $longest = mb_strlen($k);
        }

        foreach ($lines as $k => $line)
        {
            $f .= "\n".str_pad(' ', $longest - mb_strlen($k)).$k.": {$line}";
        }

        if ($return) return $f; else output::line($f);
    }

    /**
     * Insert an empty line(s).
     * --
     * @param integer $num number of new lines
     */
    static function nl($num=1)
    {
        output::line(str_repeat(PHP_EOL, ((int) $num)-1));
    }

    /*
    --- Messages ---------------------------------------------------------------
     */

    /**
     * Used to print a plain line of text to the user.
     * --
     * @param string  $message
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function line($message, $return=false)
    {
        $f = $message;
        if ($return) return $f; else output::line($f);
    }

    /**
     * Used to print a general information to the user.
     * --
     * @param string  $title  optional, if message is not provided, title will
     *                        be used as a message.
     * @param string  $message
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function info($title, $message=null, $return=false)
    {
        $f = "\e[34m{$title}\e[39m".($message ? ': '.$message : '');
        if ($return) return $f; else output::line($f);
    }

    /**
     * Used to print a warning.
     * --
     * @param string  $title  optional, if message is not provided, title will
     *                        be used as a message.
     * @param string  $message
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function warn($title, $message=null, $return=false)
    {
        $f = "\e[33m{$title}\e[39m".($message ? ': '.$message : '');
        if ($return) return $f; else output::line($f);
    }

    /**
     * Used to print an error.
     * --
     * @param string  $title  optional, if message is not provided, title will
     *                        be used as a message.
     * @param string  $message
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function error($title, $message=null, $return=false)
    {
        $f = "\e[31m{$title}\e[39m".($message ? ': '.$message : '');
        if ($return) return $f; else output::line($f);
    }

    /**
     * Used to print an information that a job was successful.
     * --
     * @param string  $title  optional, if message is not provided, title will
     *                        be used as a message.
     * @param string  $message
     * @param boolean $return weather result should be return rather than output
     * --
     * @return string
     */
    static function success($title, $message=null, $return=false)
    {
        $f = "\e[32m{$title}\e[39m".($message ? ': '.$message : '');
        if ($return) return $f; else output::line($f);
    }
}
