<?php

namespace Mysli\Users;

class Setup
{
    public function before_enable()
    {
        \Core\FS::dir_create(datpath('users'), \Core\FS::EXISTS_MERGE);
        return true;
    }

    public function before_disable()
    {
        return \Core\FS::dir_remove(datpath('users'));
    }
}
