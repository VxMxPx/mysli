<?php

namespace Mysli\Core\Lib;

class Event
{
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW    = 'low';

    // List of events to be executed
    private static $waiting = [];

    // List of events that has be triggered
    private static $history = [];

    // Master filename, it will save newly registered events.
    // Autoset on init
    private static $filename = '';

    /**
     * Will load list of events, and add them to the list.
     * --
     * @param  string $events_file
     * --
     * @return void
     */
    public static function init($events_file)
    {
        self::$filename = $events_file;
        if (file_exists($events_file)) {
            $events = json_decode(file_get_contents($events_file), true);
            if (is_array($events)) {
                self::$waiting = $events;
            }
        }
    }

    /**
     * This will permanently add particular event to the list.
     * --
     * @param  string $event
     * @param  mixed  $call
     * @param  string $priority Event::PRIORITY_HIGH, Event::PRIORITY_MEDIUM,
     *                          Event::PRIORITY_LOW
     * @param  string $filename If not provided self::$filename will be used.
     * --
     * @return boolean
     */
    public static function register($event, $call, $priority=self::PRIORITY_MEDIUM, $filename=null)
    {
        $filename = $filename ? $filename : self::$filename;

        // Get all events
        if (!file_exists($filename)) {
            return false;
        }

        $events = json_decode(file_get_contents($filename), true);
        if (!is_array($events)) {
            return false;
        }

        if (!isset($evetns[$event])) {
            $evetns[$event] = [];
        }
        if (!isset($evetns[$event][$priority])) {
            $evetns[$event][$priority] = [];
        }
        $evetns[$event][$priority][] = $call;

        return file_put_contents($filename, json_encode($events));
    }

    /**
     * Wait for paticular event to happened - then call the assigned function / method.
     * --
     * @param   string  $event    Name of the event you're waiting for
     * @param   mixed   $call     Can be name of the function, or array('className', 'methodName')
     * @param   boolean $priority Which priority should the event be:
     *                            Event::PRIORITY_HIGH, Event::PRIORITY_MEDIUM,
     *                            Event::PRIORITY_LOW
     * --
     * @return  void
     */
    public static function on($event, $call, $priority=self::PRIORITY_MEDIUM)
    {
        if (!isset(self::$waiting[$event])) {
            self::$waiting[$event] = [];
        }
        if (!isset(self::$waiting[$event][$priority])) {
            self::$waiting[$event][$priority] = [];
        }
        self::$waiting[$event][$priority][] = $call;
    }

    /**
     * Dump currently waiting evetns, and history of executed events
     * --
     * @return array
     */
    public static function dump()
    {
        return [self::$waiting, self::$history];
    }

    /**
     * Trigger the event.
     * --
     * @param   string  $event  Which event?
     * @param   mixed   $params Shall we provide any params?
     * @return  integer Number of called functions.
     *                  Function count only if "true" was returned.
     */
    public static function trigger($event, &$params=null)
    {
        $num = 0;

        self::$history[$event][] = 'Trigger!';

        // Check if anyone at all is waiting for this event.
        if (!isset(self::$waiting[$event])) {
            return 0;
        }

        // Create new list, sorted by priority
        $events = [];
        foreach (['high', 'medium', 'low'] as $priority) {
            if (isset(self::$waiting[$event][$priority])) {
                foreach (self::$waiting[$event][$priority] as $value) {
                    $events[] = $value;
                }
            }
        }

        if (!empty($events)) {
            foreach ($events as $call) {
                self::$history[$event][] = 'Call: ' . (is_array($call) ? implode(', ', $call) : $call);
                $num = $num + (Librarian::call($call, [&$params]) ? 1 : 0);
            }
        }

        return $num;
    }
}