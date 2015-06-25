<?php

/**
 * Allows you you to handle system events.
 *
 * To wait for an events, use:
 *
 *      event::on('event::action', function ($param, $param2) {});
 *
 * To trigger an event, use:
 *
 *      event::trigger('event::action', [$param, $param2]);
 *
 * Priority can be set with:
 *
 *      event::on('event::action', 'vend.pkg.cls::mtd', event::priority_high);
 *
 * There are only two priorities, `event::priority_high` and
 * `event::priority_low`, the first will push function to the top of
 * the waiting list, the other to the bottom.
 *
 * Events can be permanenty added the the list, meaning, you don't need to use
 * `on` method on each run, rather function will be called each time event is
 * triggered regardless if you've used `on` method:
 *
 *      event::register('event::action', 'vend.pkg.cls::mtd');
 *
 * You can register multiple handlers:
 *
 *      event::register([
 *          'event::action' => 'vend.pkg.cls::mtd',
 *          'event::different' => 'vend.pkg.cls::diff_mtd'
 *      ]);
 *
 * To stop observing events, use `unregister`,
 * in same format as you've used `register`:
 *
 *      event::unregister('event::action', 'vend.pkg.cls::mtd');
 */
namespace mysli\toolkit; class event
{
    const __use = '
        .{ fs.file, json, log, exception.* }
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
     */
    static function __init($filename)
    {
        if (!file::exists($filename))
        {
            throw new exception\not_found(
                "File not found: `{$filename}`.", 1
            );
        }

        self::$registry = $filename;
        self::read();
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
        if (!isset(self::$events[$event]))
        {
            self::$events[$event] = [];
        }

        if ($priority === self::priority_low)
        {
            self::$events[$event][++self::$eid] = $call;
        }
        else
        {
            self::$events = ['eid_'.++self::$eid => $call] + self::$events;
        }

        end(self::$events[$event]);
        return key(self::$events[$event]);
    }

    /**
     * Cancel particular event.
     * --
     * @param   string   $event Name of the event to be canceled.
     * @param   callable $call
     */
    static function off($event, $call)
    {
        foreach (self::$events as $cevent => &$calls)
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
                unset(self::$events[$event]);
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
                if (!self::register($event_i, $call))
                    return false;
            }

            return true;
        }

        $events = json::decode_file(self::$registry, true);

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

        log::info("Register: `{$event}` to `{$call}`.", __CLASS__);

        self::on($event, $call, $priority);

        return self::write($events);
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
                if (!self::unregister($event_i, $call))
                    return false;
            }

            return true;
        }

        $events = json::decode_file(self::$registry, true);

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

        log::info("Unregister: `{$event}` for `{$call}`.", __CLASS__);

        self::off($event, $call);
        return self::write($events);
    }

    /**
     * Trigger an event.
     * --
     * @param  string $event  Event name.
     * @param  array  $params
     */
    static function trigger($event, array $params=[])
    {
        log::debug("Trigger: {$event}", __CLASS__);

        foreach (self::$events as $levent => $calls)
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
        self::$events = json::decode_file(self::$registry, true);
        self::$eid = count(self::$events);
    }
    /**
     * Write list of events.
     * --
     * @return boolean
     */
    static function write()
    {
        return json::encode_file(self::$registry, $events);
    }
}
