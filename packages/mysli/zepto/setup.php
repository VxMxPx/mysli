<?php

namespace Mysli\Zepto;

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
            pkgpath('mysli/zepto/assets'),
            $this->web->path('mysli/zepto')
        );
    }

    public function after_disable()
    {
        \Core\FS::dir_remove($this->web->path('mysli/zepto'));
        return true;
    }
}
