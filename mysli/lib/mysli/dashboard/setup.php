<?php

namespace Mysli\Dashboard;

class Setup
{
    public function before_enable()
    {
        \FS::dir_copy(
            libpath('mysli/dashboard/assets'),
            pubpath('mysli/dashboard'))
        );
        return true;
    }

    public function after_disable()
    {
        \FS::dir_remove(pubpath('mysli/dashboard'));
        return true;
    }
}
