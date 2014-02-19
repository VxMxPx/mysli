<?php

namespace Mysli\Dashboard;

class Setup
{
    protected $web;
    protected $event;

    public function __construct($web, $router, $event, $output)
    {
        $this->web = $web;
        $this->event = $event;
    }

    public function before_enable()
    {
        \Core\FS::dir_copy(
            libpath('mysli/dashboard/assets'),
            $this->web->path('mysli/dashboard')
        );
        $this->event->register('*/router/route:*<dashboard*>', 'mysli/dashboard::display');
        return true;
    }

    public function before_disable()
    {
        \Core\FS::dir_remove($this->web->path('mysli/dashboard'));
        $this->event->unregister('*/router/route:*<dashboard*>', 'mysli/dashboard::display');
        return true;
    }
}
