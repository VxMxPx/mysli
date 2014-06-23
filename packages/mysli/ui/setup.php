<?php

namespace Mysli\Ui;

class Setup
{
    private $web;

    public function __construct($web)
    {
        $this->web = $web;
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
        \Core\FS::dir_remove($this->web->path('mysli/ui'));
        return true;
    }
}
