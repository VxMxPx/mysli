<?php

namespace mysli\toolkit; class event
{
    const __use = '
        .{ fs.file -> file, json, log, exception.event }
    ';

    const priority_low = 'low';
    const priority_high = 'high';

    /**
     * Actual filename to the list of registered events.
     * --
     * @var string
     */
    private static $registry;

    /**
     * Actual list of registered events.
     * --
     * @var array
     */
    private static $events = [];

    /**
     * Unique event identifier to call event off later.
     * --
     * @var integer
     */
    private static $eid = 0;


    /**
     * Init class with registry filename.
     * --
     * @param string $filename
     * --
     * @throws mysli\toolkit\exception\event 10 File not found.
     */
    static function __init($filename=null)
    {
        $filename = $filename ?: MYSLI_CFGPATH."/toolkit.events.json";

        if (!file::exists($filename))
            throw new exception\event(
                "File not found: `{$filename}`.", 10
            );

        static::$registry = $filename;
        static::read();
    }

    /**
     * Wait for a particular event to happen,
     * then call the assigned function / method.
     * --
     * @param string   $event
     *        Name of the event you're waiting for.
     *
     * @param callable $call
     *
     * @param boolean  $priority
     *        Which priority should the event be:
     *        event::priority_high, event::priority_low
     * --
     * @return string
     *         Event ID in the stack, it can be used
     *         to call particular event off.
     */
    static function on($event, $call, $priority=self::priority_low)
    {
        if (!isset(static::$events[$event]))
        {
            static::$events[$event] = [];
        }

        if ($priority === self::priority_low)
        {
            static::$events[$event][++static::$eid] = $call;
        }
        else
        {
            static::$events = ['eid_'.++static::$eid => $call] + static::$events;
        }

        end(static::$events[$event]);
        return key(static::$events[$event]);
    }

    /**
     * Cancel particular event.
     * --
     * @param   string   $event Name of the event to be canceled.
     * @param   callable $call
     */
    static function off($event, $call)
    {
        foreach (static::$events as $cevent => &$calls)
        {
            if ($cevent !== $event)
            {
                continue;
            }

            foreach ($calls as $call_id => $ccall)
            {
                if ($call === $ccall)
                {
                    unset($calls[$call_id]);
                }
            }

            // In case all events were unset, the main element
            // should be removed also.
            if (!$calls)
            {
                unset(static::$events[$event]);
            }
        }
    }

    /**
     * Permanently add particular event to the list.
     * --
     * @param  string|array $event
     *         List in format `event => call`.
     *
     * @param  string $call
     *         In format: `vendor.package.class::method`.
     *
     * @param  string $priority
     *         event::priority_high || event::priority_low
     * --
     * @return boolean
     */
    static function register($event, $call=null, $priority=self::priority_low)
    {
        if (is_array($event))
        {
            foreach ($event as $event_i => $call)
            {
                if (!static::register($event_i, $call))
                    return false;
            }

            return true;
        }

        $events = json::decode_file(static::$registry, true);

        if (!isset($events[$event]))
        {
            $events[$event] = [];
        }

        // Prevent duplicates...
        if (in_array($call, $events[$event]))
        {
            return true;
        }

        if ($priority === self::priority_low)
        {
            $events[$event][] = $call;
        }
        else
        {
            array_unshift($events[$event], $call);
        }

        \log::info("Register: `{$event}` to `{$call}`.", __CLASS__);

        static::on($event, $call, $priority);

        return static::write($events);
    }

    /**
     * Permanently remove particular event from the list.
     * --
     * @param  string|array $event
     *         List in format `event => call`.
     *
     * @param  string $call
     *         In format: `vendor.package.class::method`.
     * --
     * @return boolean
     */
    static function unregister($event, $call=null)
    {
        if (is_array($event))
        {
            foreach ($event as $event_i => $call)
            {
                if (!static::unregister($event_i, $call))
                    return false;
            }

            return true;
        }

        $events = json::decode_file(static::$registry, true);

        foreach ($events as $cevent => &$calls)
        {
            if ($cevent !== $event)
            {
                continue;
            }

            foreach ($calls as $call_id => $ccall)
            {
                if ($ccall === $call)
                {
                    unset($calls[$call_id]);
                }
            }

            // In case all events were unset, the main element
            // should be removed also.
            if (!$calls)
            {
                unset($events[$event]);
            }
        }

        \log::info("Unregister: `{$event}` for `{$call}`.", __CLASS__);

        static::off($event, $call);
        return static::write($events);
    }

    /**
     * Trigger an event.
     * --
     * @param  string $event  Event name.
     * @param  array  $params
     */
    static function trigger($event, array $params=[])
    {
        \log::debug("Trigger: {$event}", __CLASS__);

        foreach (static::$events as $levent => $calls)
        {
            if (strpos($levent, '*') !== false)
            {
                $regex = preg_quote($levent, '/');
                $regex = '/^' . str_replace('\\*', '.*?', $regex) . '$/i';
            }
            else
            {
                $regex = false;
            }

            // Check if anyone at all is waiting for this event.
            if (!$regex && $event !== $levent)
            {
                continue;
            }

            if ($regex && !preg_match($regex, $event))
            {
                continue;
            }

            foreach ($calls as $call_id => $call)
            {
                call_user_func_array($call, $params);
            }
        }
    }

    /*
    --- Read / Write -----------------------------------------------------------
     */

    /**
     * Read list of events.
     * This will erase all temporary events set with `on`!
     */
    static function read()
    {
        static::$events = json::decode_file(static::$registry, true);
        static::$eid = count(static::$events);
    }
    /**
     * Write list of events.
     * --
     * @return boolean
     */
    static function write()
    {
        return json::encode_file(static::$registry, $events);
    }
}
