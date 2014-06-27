<?php

namespace Mysli\Ui;

class Setup
{
    private $web;
    private $event;

    public function __construct($web, $event)
    {
        $this->web = $web;
        $this->event = $event;
    }

    public function before_enable()
    {
        return \Core\FS::dir_copy(
            pkgpath('mysli/ui/assets'),
            $this->web->path('mysli/ui')
        );
    }

    public function after_disable()
    {
        $this->event->unregister('mysli/web/route:*<mysli-ui-examples*>', 'mysli/ui->examples');
        \Core\FS::dir_remove($this->web->path('mysli/ui'));
        return true;
    }
}
