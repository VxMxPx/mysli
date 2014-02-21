<?php

namespace Mysli\Router;

class Setup
{
    protected $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function before_enable()
    {
        return $this->event->register('*/web/index:start', 'mysli/router::route');
    }

    public function before_disable()
    {
        return $this->event->unregister('*/web/index:start', 'mysli/router::route');
    }
}
