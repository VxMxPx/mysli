<?php

namespace Mysli\Core\Lib;

class Log
{
    // All log items
    protected $logs = [];

    protected $benchmark;
    protected $event;

    /**
     * Construct LOG
     * --
     * @param array $config
     *   - none
     * @param array $dependencies
     *   - benchmark
     *   - event
     */
    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->benchmark = $dependencies['benchmark'];
        $this->event     = $dependencies['event'];
    }

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
    public function add($message, $type, $file, $line)
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
        if ($this->event) {
            $this->event->trigger(
                '/mysli/core/lib/log->add',
                $log_item
            );
        }

        // Add item to the stack
        $this->logs[] = $log_item;
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
    public function info($message, $file, $line)
        { return $this->add($message, 'info',  $file, $line); }
    public function warn($message, $file, $line)
        { return $this->add($message, 'warn',  $file, $line); }
    public function error($message, $file, $line)
        { return $this->add($message, 'error', $file, $line); }

    /**
     * Is particular (or any) log message set?
     * --
     * @param  mixed $type info|warn|error -- or false
     * --
     * @return boolean
     */
    public function has($type=false)
    {
        if (!$type) {
            return !empty($this->logs);
        }
        else {
            if (empty($this->logs)) { return false; }
            if (!is_array($type))   { $type = array($type); }

            foreach ($this->logs as $log) {
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
    public function as_array($type=false)
    {
        if (!$this->has())     { return array(); }
        if (!$type)           { return $this->logs; }
        if (!is_array($type)) { $type = array($type); }

        $collection = array();
        foreach ($this->logs as $log) {
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
    public function as_string($type=false, $line_break="\n")
    {
        $output = '';
        $logs   = $this->as_array($type);

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
    public function as_html($template, $type=false)
    {
        $output = '';
        $logs   = $this->as_array($type);

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
    public function add_benchmarks()
    {
        // Add Memory usage..
        $memory       = $this->benchmark->get_memory_usage();
        $memory_bytes = $this->benchmark->get_memory_usage(false);
        $this->info(
            "Memory usage: {$memory_bytes} bytes / " . $memory .
            " the memory limist is: " . ini_get('memory_limit'),
            __FILE__, __LINE__
        );

        // Add Total Loading Time Of System
        $sys_timer = $this->benchmark->get_timer('/mysli/core');
        $this->add(
            "Total processing time: {$sys_timer}",
            ((float)$sys_timer > 5 ? 'warn' : 'info' ),
            __FILE__, __LINE__
        );
    }
}
