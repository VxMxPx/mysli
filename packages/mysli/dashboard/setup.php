<?php

namespace Mysli\Dashboard;

class Setup
{
    protected $web;
    protected $event;

    public function __construct($web, $event)
    {
        $this->web = $web;
        $this->event = $event;
    }

    public function before_enable()
    {
        \Core\FS::dir_copy(
            pkgpath('mysli/dashboard/assets'),
            $this->web->path('mysli/dashboard')
        );
        $this->event->register('mysli/web/route:*<dashboard*>', 'mysli/dashboard::display');
        return true;
    }

    public function before_disable()
    {
        \Core\FS::dir_remove($this->web->path('mysli/dashboard'));
        $this->event->unregister('mysli/web/route:*<dashboard*>', 'mysli/dashboard::display');
        return true;
    }
}
