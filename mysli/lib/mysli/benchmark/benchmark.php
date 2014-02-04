<?php

namespace Mysli;

class Benchmark
{
    protected $timers;

    /**
     * MircoTime start
     * --
     * @param  string $name  We should give unique name to our timer
     * --
     * @return integer
     */
    public function set_timer($name)
    {
        $temp = explode(' ', microtime());
        $this->timers[$name] = $temp[1] + $temp[0];
        return $this->timers[$name];
    }

    /**
     * Return the time that was set in "set_timer"
     * --
     * @param  string $name  Name of the timer
     * --
     * @return string
     */
    public function get_timer($name)
    {
        if (isset($this->timers[$name]))
        {
            $start = $this->timers[$name];
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
    public function get_memory_usage($formated = true, $peak = true)
    {
        $memory = 0;

        if ($peak && function_exists('memory_get_peak_usage')) {
            $memory = memory_get_peak_usage(true);
        }
        elseif (function_exists('memory_get_usage')) {
            $memory = memory_get_usage(true);
        }

        return ($formated) ? \Core\FS::format_size($memory) : $memory;
    }
}
