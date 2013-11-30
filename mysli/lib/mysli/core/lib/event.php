<?php

namespace Mysli\Core\Lib;

class Event
{
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW    = 'low';

    // List of events to be executed
    protected $waiting = [];

    // List of events that has be triggered
    protected $history = [];

    // Master filename, it will save newly registered events.
    // Autoset on init
    protected $filename = '';

    protected $librarian;

    /**
     * Construct EVENT
     * --
     * @param array $config
     *   - eventfile = Master filename it will save newly registered events.
     * @param array $dependencies
     *   - librarian
     */
    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->filename = $config['eventfile'];
        if (!file_exists($this->filename)) {
            throw new \Mysli\Core\FileNotFoundException(
                "File not found: '{$this->filename}'"
            );
        }
        $events = json_decode(file_get_contents($this->filename), true);
        if (is_array($events)) {
            $this->waiting = $events;
        }

        $this->librarian = $dependencies['librarian'];
    }

    /**
     * This will permanently add particular event to the list.
     * --
     * @param  string $event
     * @param  mixed  $call
     * @param  string $priority Event::PRIORITY_HIGH, Event::PRIORITY_MEDIUM,
     *                          Event::PRIORITY_LOW
     * @param  string $filename If not provided $this->filename will be used.
     * --
     * @return boolean
     */
    public function register(
        $event,
        $call,
        $priority = self::PRIORITY_MEDIUM,
        $filename = null
    ) {
        $filename = $filename ? $filename : $this->filename;

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
    public function on($event, $call, $priority = self::PRIORITY_MEDIUM)
    {
        if (!isset($this->waiting[$event])) {
            $this->waiting[$event] = [];
        }
        if (!isset($this->waiting[$event][$priority])) {
            $this->waiting[$event][$priority] = [];
        }
        $this->waiting[$event][$priority][] = $call;
    }

    /**
     * Dump currently waiting evetns, and history of executed events
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
                }
                $this->history[$event][] = 'Call: ' . (is_array($call) ? implode(', ', $call) : $call);
                $num += ($this->librarian->call($call[0], $call[1], [&$params]) ? 1 : 0);
            }
        }

        return $num;
    }
}
