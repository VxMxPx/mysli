<?php

namespace Mysli\Core\Lib;

class Benchmark
{
    private static $timers;

    /**
     * MircoTime start
     * --
     * @param  string $name  We should give unique name to our timer
     * --
     * @return integer
     */
    public static function set_timer($name)
    {
        $temp = explode(' ', microtime());
        self::$timers[$name] = $temp[1] + $temp[0];
        return self::$timers[$name];
    }

    /**
     * Return the time that was set in "set_timer"
     * --
     * @param  string $name  Name of the timer
     * --
     * @return string
     */
    public static function get_timer($name)
    {
        if (isset(self::$timers[$name]))
        {
            $start = self::$timers[$name];
            $temp  = explode(' ', microtime());
            $total = $temp[0] + $temp[1] - $start;
            $total = sprintf('%.3f',  $total);

            return $total;
        }
    }

    /**
     * Return memory usage
     * --
     * @param  boolean $peak
     * @param  boolean $formated
     * --
     * @return string
     */
    public static function get_memory_usage($formated=true, $peak=true)
    {
        $memory = 0;

        if ($peak && function_exists('memory_get_peak_usage')) {
            $memory = memory_get_peak_usage(true);
        }
        elseif (function_exists('memory_get_usage')) {
            $memory = memory_get_usage(true);
        }

        return ($formated) ? FS::format_size($memory) : $memory;
    }
}