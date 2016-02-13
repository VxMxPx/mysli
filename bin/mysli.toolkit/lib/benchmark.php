<?php

namespace mysli\toolkit; class benchmark
{
    const __use = <<<fin
    .fs.{ fs }
fin;

    /**
     * List of defined timers.
     * --
     * @var array
     */
    protected static $timers = [];

    /**
     * MircoTime start.
     * --
     * @param  string $name An unique timer name.
     */
    static function set_timer($name)
    {
        $mtimer = explode(' ', microtime(), 2);
        static::$timers[$name] = $mtimer[1] + $mtimer[0];
    }

    /**
     * Return difference between now and start (set in set_timer).
     * --
     * @param string|float $from
     *        String to get time from previously defined timer.
     *        Float to calculate difference now
     * --
     * @return string
     */
    static function get_time($name)
    {
        if (is_float($name))
        {
            $start = $name;
        }
        elseif (isset(static::$timers[$name]))
        {
            $start = $name;
        }
        else
        {
            return null;
        }

        $mtimer  = explode(' ', microtime());
        $total = $mtimer[0] + $mtimer[1] - $start;
        $total = sprintf('%.3f',  $total);

        return $total;
    }

    /**
     * Return memory usage
     * --
     * @param boolean $peak
     * @param boolean $formated
     * --
     * @return string
     */
    public static function get_memory_usage($formated=true, $peak=true)
    {
        $memory = 0;

        if ($peak && function_exists('memory_get_peak_usage')) {
            $memory = memory_get_peak_usage();
        }
        elseif (function_exists('memory_get_usage')) {
            $memory = memory_get_usage();
        }

        return ($formated) ? implode(' ', fs::format_size($memory)) : $memory;
    }
}
