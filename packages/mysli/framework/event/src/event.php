<?php

namespace mysli\framework\event;

__use(__namespace__, '
    mysli.framework.fs/fs,file
    mysli.framework.json
    mysli.framework.exception/* -> framework\exception\*
');

class event {

    const priority_low = 'low';
    const priority_high = 'high';

    private static $registry;
    private static $events = [];
    private static $eid;

    /**
     * Init class with registry filename
     * @param string $filename
     */
    static function __init($filename) {
        if (!file::exists($filename)) {
            throw new framework\exception\not_found(
                "File not found: `{$filename}`.", 1);
        }
        self::$registry = $filename;
        self::reload();
    }
    /**
     * Wait for particular event to happened,
     * then call the assigned function / method.
     * @param   string  $event    Name of the event you're waiting for
     * @param   mixed   $call     Can be:
     *                            - callable
     *                            - string: vendor\package\class::method
     * @param   boolean $priority Which priority should the event be:
     *                            event::priority_high, event::priority_low
     * @return  string            Event ID in the stack, it can be used to
     *                            call particular event off
     */
    static function on($event, $call, $priority=self::priority_low) {

        if (!isset(self::$events[$event])) {
            self::$events[$event] = [];
        }

        if ($priority === self::priority_low) {
            self::$events[$event][++self::$eid] = $call;
        } else {
            self::$events = ['eid_'.++self::$eid => $call] + self::$events;
        }

        end(self::$events[$event]);
        return key(self::$events[$event]);
    }
    /**
     * Cancel particular event.
     * @param   string  $event Name of the event to be cancelled.
     * @param   mixed   $call  Can only be either:
     *                         - vendor\package\class::method
     *                         - event id (from ::on() call)
     * @return  null
     */
    static function off($event, $call) {
        foreach (self::$events as $cevent => &$calls) {
            if ($cevent !== $event) {
                continue;
            }
            foreach ($calls as $call_id => $ccall) {
                if ($call === $ccall) {
                    unset($calls[$call_id]);
                }
            }
            // In case all events were unset, the main element
            // should be removed also.
            if (!$calls) {
                unset(self::$events[$event]);
            }
        }
    }
    /**
     * Permanently add particular event to the list.
     * @param  string $event
     * @param  string $call     in format: vendor\package\class::method
     * @param  string $priority event::priority_high || event::priority_low
     * @return boolean
     */
    static function register($event, $call, $priority=self::priority_low) {
        $events = json::decode_file(self::$registry, true);

        if (!isset($events[$event])) {
            $events[$event] = [];
        }

        // Prevent duplicates...
        if (in_array($call, $events[$event])) {
            return true;
        }

        if ($priority === self::priority_low) {
            $events[$event][] = $call;
        } else {
            array_unshift($events[$event], $call);
        }

        self::on($event, $call, $priority);
        return self::write($events);
    }
    /**
     * Permanently remove particular event from the list.
     * @param  string $event
     * @param  string $call  in format: vendor\package\class::method
     * @return boolean
     */
    static function unregister($event, $call) {
        $events = json::decode_file(self::$registry, true);

        foreach ($events as $cevent => &$calls) {
            if ($cevent !== $event) {
                continue;
            }
            foreach ($calls as $call_id => $ccall) {
                if ($ccall === $call) {
                    unset($calls[$call_id]);
                }
            }
            // In case all events were unset, the main element
            // should be removed also.
            if (!$calls) {
                unset($events[$event]);
            }
        }
        self::off($event, $call);
        return self::write($events);
    }
    /**
     * Trigger an event.
     * @param  string $event  Event name.
     * @param  array  $params
     * @return null
     */
    static function trigger($event, array $params=[]) {

        foreach (self::$events as $levent => $calls) {

            if (strpos($levent, '*') !== false) {
                $regex = preg_quote($levent, '/');
                $regex = '/^' . str_replace('\\*', '.*?', $regex) . '$/i';
            } else {
                $regex = false;
            }

            // Check if anyone at all is waiting for this event.
            if (!$regex && $event !== $levent) {
                continue;
            }
            if ($regex && !preg_match($regex, $event)) {
                continue;
            }

            foreach ($calls as $call_id => $call) {
                call_user_func_array($call, $params);
            }
        }
    }
    /**
     * Reload list of events.
     * This will erase all temporary events set with `on`!
     * @return null
     */
    static function reload() {
        self::$events = json::decode_file(self::$registry, true);
        self::$eid = count(self::$events);
    }
    /**
     * Write list of events.
     * @param  array $events
     * @return boolean
     */
    static function write(array $events=null) {
        if (!$events) {
            $events = self::$events;
        }
        return json::encode_file(self::$registry, $events);
    }
}
