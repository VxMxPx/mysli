<?php

namespace mysli\toolkit\cli; class ui
{
    const __use = <<<fin
        .{ exception.cli }
        .cli.{ output, input }
        .type.{ intg }
fin;

    /**
     * Unordered list.
     * --
     * @example
     * - item
     * - item
     * - item
     */
    const list_unordered = "ul";

    /**
     * Ordered list.
     * --
     * @example
     * 1. item
     * 2. item
     * 3. item
     */
    const list_ordered = 'ol';

    /**
     * Aligned list.
     * --
     * @example
     * Slovenia : Ljubljana
     * Austria  : Vienna
     * Russia   : Moscow
     * Italy    : Rome
     */
    const list_aligned = 'al';


    /**
     * Indent size.
     * --
     * @var integer
     */
    protected static $indent_size = 2;

    /**
     * Buffered text so far.
     * --
     * @var array
     */
    protected static $buffer = [];

    /**
     * Is text being buffered.
     * --
     * @var boolean
     */
    protected static $is_buffering = false;


    /**
     * Print title.
     * --
     * @param string  $string
     * @param integer $indent
     */
    static function title($string, $indent=0)
    {
        static::strong("\n{$string}\n", $indent);
    }

    /**
     * Make output more important.
     * --
     * @param string  $string
     * @param integer $indent
     */
    static function strong($string, $indent=0)
    {
        static::add("\e[1m{$string}\e[0m", $indent);
    }

    /**
     * Print a regular line.
     * --
     * @param string  $string
     * @param integer $indent
     */
    static function line($string, $indent=0)
    {
        static::add($string, $indent);
    }

    /**
     * Print a general information to the user.
     * --
     * @param string $title
     *
     * @param string $message
     *        Optional, if message is not provided,
     *        title will be used as a message.
     *
     * @param integer $indent
     */
    static function info($title, $message=null, $indent=0)
    {
        $f = "\e[34m{$title}".($message ? ":\e[39m {$message}" : "\e[39m");
        static::add($f, $indent);
    }

    /**
     * Print a warning.
     * --
     * @param string $title
     *
     * @param string $message
     *        Optional, if message is not provided,
     *        title will be used as a message.
     *
     * @param integer $indent
     */
    static function warning($title, $message=null, $indent=0)
    {
        $f = "\e[33m{$title}".($message ? ":\e[39m {$message}" : "\e[39m");
        static::add($f, $indent);
    }

    /**
     * Print an error.
     * --
     * @param string $title
     *
     * @param string $message
     *        Optional, if message is not provided,
     *        title will be used as a message.
     *
     * @param integer $indent
     */
    static function error($title, $message=null, $indent=0)
    {
        $f = "\e[31m{$title}".($message ? ":\e[39m {$message}" : "\e[39m");
        static::add($f, $indent);
    }

    /**
     * Print an information that a job was successful.
     * --
     * @param string $title
     *
     * @param string $message
     *        Optional, if message is not provided,
     *        title will be used as a message.
     *
     * @param integer $indent
     */
    static function success($title, $message=null, $indent=0)
    {
        $f = "\e[32m{$title}".($message ? ":\e[39m {$message}" : "\e[39m");
        static::add($f, $indent);
    }

    /**
     * Print various lists.
     * --
     * @param array   $list
     * @param string  $type
     * @param integer $indent
     * --
     * @throws mysli\toolkit\exception\cli 10 Invalid list type.
     */
    static function lst(array $list, $type=self::list_unordered, $indent=0)
    {
        switch ($type)
        {
            case static::list_unordered:
            case static::list_ordered:
                $i = 0;
                foreach ($list as $line)
                    is_array($line)
                        ? static::lst($line, $type, $indent+1)
                        : static::add(
                            ($type === static::list_ordered ? (++$i).'.' : '-').
                            " {$line}", $indent);
            break;

            case static::list_aligned:
                $separator = ' : ';

                $longest = 0;
                foreach ($list as $key => $val)
                    $longest = strlen($key) > $longest
                        ? strlen($key)
                        : $longest;

                foreach ($list as $key => $value)
                {
                    if (is_array($value))
                    {
                        static::add($key, $indent);
                        static::lst($value, static::list_aligned, $indent+1);
                        continue;
                    }
                    elseif (is_bool($value))
                        $value = $value ? 'true' : 'false';
                    elseif (is_string($value))
                        $value = "\"{$value}\"";

                    static::add(
                        $key.str_repeat(' ', $longest - strlen($key)).
                            $separator.$value,
                        $indent);
                }
            break;

            default:
                throw new exception\cli("Invalid list type: `{$type}`.", 10);
        }
    }

    /**
     * Output a progress bar.
     * --
     * @param integer $current Current position
     * @param integer $max     Maximum position
     * @param string  $title   Progress bar title
     * @param array   $markers Markers [ filled, empty ]
     */
    static function progress($current, $max, $title, $markers=['#', ' '])
    {
        $percent = (int) floor(intg::get_percent($current, $max));
        $paint   = (int) floor( $percent / 2 );

        $bar = '';
        if ($paint > 0)  $bar .= str_repeat($markers[0], $paint);
        if ($paint < 50) $bar .= str_repeat($markers[1], 50-$paint);

        $title = $title ? "{$title} " : '';

        static::progress_f('%s[%s] (%s%%)', [$title, $bar, $percent]);
    }

    /**
     * Output formatted progress bar.
     * --
     * @param string $format
     * @param array  $variables
     */
    static function progress_f($format, array $variables)
    {
        output::line(vsprintf($format, $variables)."\r", false);
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

    /**
     * Set buffering ON.
     */
    static function buffer()
    {
        static::$is_buffering = true;
    }

    /**
     * Flush buffer and switch buffering off.
     * --
     * @param boolean $return Return rather thna print!
     */
    static function flush($return=false)
    {
        $buffer = implode("\n", static::$buffer);
        static::clear();

        if ($return) return $buffer;
        else         output::line($buffer);
    }

    /**
     * Clear buffer without printing anything!
     */
    static function clear()
    {
        static::$buffer = [];
        static::$is_buffering = false;
    }

    /**
     * Add line(s) to the buffer/flush buffer, ...
     * --
     * @param string  $string
     * @param integer $indent
     */
    static function add($string, $indent=0)
    {
        // Add indentation!
        $string = str_repeat(' ', $indent*static::$indent_size).$string;

        static::$buffer[] = $string;

        if (!static::$is_buffering)
        {
            static::flush();
        }
    }
}
