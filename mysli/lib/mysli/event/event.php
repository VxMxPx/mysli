<?php

namespace Mysli;

class Event
{
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW    = 'low';

    // List of events to be executed
    protected $waiting = [];

    // List of events that has be triggered
    protected $history = [];

    // Master file, where newly registered events will be saved.
    // Will be set when constructed.
    protected $filename = '';

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
     * @throws \Mysli\Event\FileException If registry file not found.
     * @throws \Mysli\Event\DataException If registry file doesn't contain a valid json.
     * --
     * @return array
     */
    protected function get_list()
    {
        // Get all events,
        // we don't want to manipulate $this->waiting, because some events
        // are added with ->on() method, and are not meant to be saved.
        if (!file_exists($this->filename)) {
            throw new \Mysli\Event\FileException(
                "File not found: `{$this->filename}`.", 1
            );
        }

        $events = json_decode(file_get_contents($this->filename), true);
        if (!is_array($events)) {
            throw new \Mysli\Event\DataException(
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
     * @param  string $priority Event::PRIORITY_HIGH, Event::PRIORITY_MEDIUM,
     *                          Event::PRIORITY_LOW
     * --
     * @return boolean
     */
    public function register($event, $call, $priority = self::PRIORITY_MEDIUM)
    {
        $events = $this->get_list();

        if (!isset($events[$event])) {
            $events[$event] = [];
        }

        if (!isset($events[$event][$priority])) {
            $events[$event][$priority] = [];
        }

        $events[$event][$priority][] = $call;

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
        $events = $this->get_list();

        foreach ($events as $event_key => &$event_data)
            if ($event_key === $event)
                foreach ($event_data as $priority => &$callers)
                    if (in_array($call, $callers))
                        unset($callers[array_search($call, $callers)]);

        $this->off($event, $call);
        return !!file_put_contents($this->filename, json_encode($events));
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
     *                            Event::PRIORITY_HIGH, Event::PRIORITY_MEDIUM,
     *                            Event::PRIORITY_LOW
     * --
     * @return  integer           Event ID in the stack, it can be used to
     *                            call particular event off
     */
    public function on($event, $call, $priority = self::PRIORITY_MEDIUM)
    {
        if (!isset($this->waiting[$event])) {
            $this->waiting[$event] = [];
        }
        if (!isset($this->waiting[$event][$priority])) {
            $this->waiting[$event][$priority] = [];
        }
        $this->waiting[$event][$priority][] = $call;

        // Return ID
        end($this->waiting[$event][$priority]);
        return key($this->waiting[$event][$priority]);
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
        // What a beauty! :P
        foreach ($this->waiting as $event_key => &$event_data)
            if ($event_key === $event)
                foreach ($event_data as $priority => &$callers)
                    foreach ($callers as $id => $callback)
                        if (is_integer($call) && $call === $id)
                            unset($callers[$id]);
                        elseif (is_string($callback) && $call === $callback)
                            unset($callers[$id]);
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

        // Check if anyone at all is waiting for this event.
        if (!isset($this->waiting[$event])) {
            return 0;
        }

        // Create new list, sorted by priority
        $events = [];
        foreach (['high', 'medium', 'low'] as $priority) {
            if (isset($this->waiting[$event][$priority])) {
                foreach ($this->waiting[$event][$priority] as $value) {
                    $events[] = $value;
                }
            }
        }

        if (!empty($events)) {
            foreach ($events as $call) {
                if (!is_string($call) && !is_array($call) && is_callable($call)) {
                    $num += $call($params) ? 1 : 0;
                    continue;
                } else if (!is_array($call)) {
                    $call = explode('::', $call, 2);
                }
                $this->history[$event][] = 'Call: ' . implode('::', $call);
                $num += ($this->librarian->call($call[0], $call[1], [&$params]) ? 1 : 0);
            }
        }

        return $num;
    }
}
