<?php

namespace Mysli\Core\Lib;

class Log
{
    // All log items
    private static $logs = [];

    /**
     * Add System Log Message.
     * --
     * @param   string  $message Plain english message
     * @param   string  $type    info|warn|error == information, warning, error
     * @param   string  $file    Use __FILE__
     * @param   integer $line    Use __LINE__
     * --
     * @return  void
     */
    public static function add($message, $type, $file, $line)
    {
        $type = strtolower($type);

        // Create new item
        $log_item = array
        (
            'date_time' => date('Y-m-d H:i:s'),
            'type'      => $type,
            'message'   => $message,
            'line'      => $line,
            'file'      => $file
        );

        // Trigger event telling to add new item
        Event::trigger('/mysli/core/lib/log::add', $log_item);

        // Add item to the stack
        self::$logs[] = $log_item;
    }

    /**
     * Add information, warning, error to the log
     * --
     * @param  string  $message  Plain english message
     * @param  string  $file     Use __FILE__
     * @param  integer $line     Use __LINE__
     * --
     * @return void
     */
    public static function info($message, $file, $line) { return self::add($message, 'info', $file, $line); }
    public static function warn($message, $file, $line) { return self::add($message, 'warn', $file, $line); }
    public static function error($message, $file, $line) { return self::add($message, 'error', $file, $line); }

    /**
     * Is particular (or any) log message set?
     * --
     * @param  mixed $type info|warn|error -- or false
     * --
     * @return boolean
     */
    public static function has($type=false)
    {
        if (!$type) {
            return !empty(self::$logs);
        }
        else {
            if (empty(self::$logs)) { return false; }
            if (!is_array($type))   { $type = array($type); }

            foreach (self::$logs as $log) {
                if (in_array($log['type'], $type)) { return true; }
            }
        }
    }

    /**
     * Return particular type of logs, or all of them, as an array.
     * --
     * @param  mixed $type array info|warn|error -- or false
     * --
     * @return array
     */
    public static function as_array($type=false)
    {
        if (!self::has())     { return array(); }
        if (!$type)           { return self::$logs; }
        if (!is_array($type)) { $type = array($type); }

        $collection = array();
        foreach (self::$logs as $log) {
            if (in_array($log['type'], $type)) { $collection[] = $log; }
        }

        return $collection;
    }

    /**
     * Return particular type of logs, or all of them, as string.
     * --
     * @param  mixed  $type       -- array inf|war|err -- or false
     * @param  string $line_break -- \n or <br>
     * --
     * @return string
     */
    public static function as_string($type=false, $line_break="\n")
    {
        $output = '';
        $logs   = self::as_array($type);

        foreach ($logs as $log) {
            $out_item = [''];
            $out_item[] = $log['date_time'] . ' - ' . $log['type'];
            $out_item[] = $log['message'];
            $out_item[] = $log['file'] . ' - ' . $log['line'];

            $output .= implode($line_break, $out_item);
            $output .= str_repeat($line_break, 2) . str_repeat('-', 75) . $line_break;
        }

        return $output;
    }

    /**
     * Retrun particular type of logs, or all of them, as string html!
     * --
     * @param  string  $template
     * @param  mixed   $type
     * --
     * @return string
     */
    public static function as_html($template, $type=false)
    {
        $output = '';
        $logs   = self::as_array($type);

        foreach ($logs as $log) {
            $output .= str_replace(
                [
                    '{type}',
                    '{date_time}',
                    '{file}',
                    '{line}',
                    '{message}'
                ],
                [
                    $log['type'],
                    $log['date_time'],
                    $log['file'],
                    $log['line'],
                    $log['message']
                ],
                $template
            );
        }

        return $output;
    }

    /**
     * Will add benchmark informations to log
     * --
     * @return void
     */
    public static function add_benchmarks()
    {
        // Add Memory usage..
        $memory       = Benchmark::get_memory_usage();
        $memory_bytes = Benchmark::get_memory_usage(false);
        self::info("Memory usage: {$memory_bytes} bytes / " . $memory .
                    " the memory limist is: " . ini_get('memory_limit'), __FILE__, __LINE__);

        // Add Total Loading Time Of System
        $sys_timer = Benchmark::get_timer('/mysli/core');
        self::add(
            "Total processing time: {$sys_timer}",
            ((float)$sys_timer > 5 ? 'warn' : 'info' ),
            __FILE__, __LINE__);
    }
}