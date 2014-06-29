<?php

namespace Mysli\Ui;

class Setup
{
    private $web;
    private $event;
    private $tplp;

    public function __construct(\Mysli\Tplp\Tplp $tplp, \Mysli\Web\Web $web, \Mysli\Event\Event $event)
    {
        $this->web = $web;
        $this->event = $event;
        $this->tplp = $tplp;
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
        $this->tplp->remove_cache();
        $this->event->unregister('mysli/web/route:*<mysli-ui-examples*>', 'mysli/ui->examples');
        \Core\FS::dir_remove($this->web->path('mysli/ui'));
        return true;
    }
}
