<?php

namespace Mysli\Dashboard;

class Setup
{
    public function before_enable()
    {
        \Core\FS::dir_copy(
            libpath('mysli/dashboard/assets'),
            pubpath('mysli/dashboard')
        );
        return true;
    }

    public function after_disable()
    {
        \Core\FS::dir_remove(pubpath('mysli/dashboard'));
        return true;
    }
}
