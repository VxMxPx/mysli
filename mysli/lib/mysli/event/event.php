<?php

namespace Mysli;

class Event
{
    // When `high` is used, the event will be added to the begining of the event list.
    // When `low` is used, the event will be added to the end of the event list.
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_LOW    = 'low';

    // List of events to be executed
    protected $waiting = [];

    // List of events that has been triggered
    protected $history = [];

    // Master file, where newly registered events will be saved.
    // Will be set when constructed.
    protected $filename = '';

    // The ~librarian library.
    protected $librarian;

    /**
     * Construct EVENT
     * --
     * @param object $librarian ~librarian
     */
    public function __construct($librarian)
    {
        $this->filename = datpath('event/registry.json');
        $this->waiting = $this->get_list();

        $this->librarian = $librarian;
    }

    /**
     * Get list of events.
     * --
     * @throws \Core\NotFoundException If registry file not found.
     * @throws \Core\DataException If registry file doesn't contain a valid json.
     * --
     * @return array
     */
    protected function get_list()
    {
        // Get all events,
        // we don't want to manipulate $this->waiting, because some events
        // are added with ->on() method, and are not meant to be saved.
        if (!file_exists($this->filename)) {
            throw new \Core\NotFoundException(
                "File not found: `{$this->filename}`.", 1
            );
        }

        $events = json_decode(file_get_contents($this->filename), true);
        if (!is_array($events)) {
            throw new \Core\DataException(
                "Expected data to be a valid json: `{$this->filename}`.", 1
            );
        }

        return $events;
    }

    /**
     * This will permanently add particular event to the list.
     * --
     * @param  string $event
     * @param  string $call     Call in format: vendor/library::method
     * @param  string $priority Event::PRIORITY_HIGH
     *                          Event::PRIORITY_LOW
     * --
     * @return boolean
     */
    public function register($event, $call, $priority = self::PRIORITY_LOW)
    {
        $events = $this->get_list();

        if (!isset($events[$event])) {
            $events[$event] = [];
        }

        if ($priority === self::PRIORITY_LOW) {
            $events[$event][] = $call;
        } else {
            array_unshift($events[$event], $call);
        }

        // if (!isset($events[$event][$priority])) {
        //     $events[$event][$priority] = [];
        // }

        // $events[$event][$priority][] = $call;

        $this->on($event, $call, $priority);
        return !!file_put_contents($this->filename, json_encode($events));
    }

    /**
     * This will permanently remove particular event from the list.
     * --
     * @param  string $event
     * @param  string $call     Call in format: vendor/library::method
     * --
     * @return boolean
     */
    public function unregister($event, $call)
    {
        $events_collection = $this->get_list();

        foreach ($events_collection as $event_from_collection => &$calls_array) {
            if ($event_from_collection !== $event) {
                continue;
            }
            foreach ($calls_array as $call_id => $call_from_collection) {
                if ($call_from_collection === $call) {
                    unset($calls_array[$call_id]);
                }
            }
            // In case all events were unset, the main element
            // should be removed also.
            if (!$calls_array) {
                unset($events_collection[$event]);
            }
        }

        $this->off($event, $call);
        return !!file_put_contents($this->filename, json_encode($events_collection));
    }

    /**
     * Wait for particular event to happened - then call the assigned function / method.
     * --
     * @param   string  $event    Name of the event you're waiting for
     * @param   mixed   $call     Can be:
     *                            - callable
     *                            - string: vendor/library::methid
     *                            - array('vendor/library', 'method')
     * @param   boolean $priority Which priority should the event be:
     *                            Event::PRIORITY_HIGH, Event::PRIORITY_LOW
     * --
     * @return  integer           Event ID in the stack, it can be used to
     *                            call particular event off
     */
    public function on($event, $call, $priority = self::PRIORITY_LOW)
    {
        if (!isset($this->waiting[$event])) {
            $this->waiting[$event] = [];
        }

        if ($priority === self::PRIORITY_LOW) {
            $this->waiting[$event][] = $call;
        } else {
            array_unshift($this->waiting[$event], $call);
        }

        // Return ID
        end($this->waiting[$event]);
        return key($this->waiting[$event]);
    }

    /**
     * Cancel particular event.
     * --
     * @param   string  $event    Name of the event you're waiting for
     * @param   mixed   $call     Can ONLY be either:
     *                            - string:  vendor/library::methid
     *                            - integer: event id (from ->on() call)
     * --
     * @return  null
     */
    public function off($event, $call)
    {
        foreach ($this->waiting as $event_from_collection => &$calls_array) {
            if ($event_from_collection !== $event) {
                continue;
            }
            foreach ($calls_array as $call_id => $call_from_collection) {
                if (is_integer($call) && $call === $call_id) {
                    unset($calls_array[$call_id]);
                }
                elseif (is_string($call) && $call === $call_from_collection) {
                    unset($calls_array[$call_id]);
                }
            }
            // In case all events were unset, the main element
            // should be removed also.
            if (!$calls_array) {
                unset($this->waiting[$event]);
            }
        }
    }

    /**
     * Dump currently waiting events, and history of executed events
     * --
     * @return array
     */
    public function dump()
    {
        return [$this->waiting, $this->history];
    }

    /**
     * Trigger the event.
     * --
     * @param   string  $event  Which event?
     * @param   mixed   $params Shall we provide any params?
     * @return  integer Number of called functions.
     *                  Function count only if "true" was returned.
     */
    public function trigger($event, &$params = null)
    {
        $num = 0;

        $this->history[$event][] = 'Trigger!';

        if (empty($this->waiting)) { return 0; }

        foreach ($this->waiting as $waiting_event => $calls_array) {

            if (strpos($waiting_event, '*') !== false) {
                $regex = preg_quote($waiting_event, '/');
                $regex =
                    '/' .
                    str_replace('\\*', '.*?', $regex) .
                    '/i';
            } else {
                $regex = false;
            }

            // Check if anyone at all is waiting for this event.
            if (!$regex && $event !== $waiting_event) { continue; }
            if ($regex && !preg_match($regex, $waiting_event)) { continue; }

            foreach ($calls_array as $call_id => $call) {
                if (!is_string($call) && !is_array($call) && is_callable($call)) {
                    $num += $call($params) ? 1 : 0;
                    continue;
                }

                if (!is_array($call)) { $call = explode('::', $call, 2); }

                $this->history[$event][] = 'Call: ' . implode('::', $call);
                $num += ($this->librarian->call($call[0], $call[1], [&$params]) ? 1 : 0);
            }
        }

        return $num;
    }
}
