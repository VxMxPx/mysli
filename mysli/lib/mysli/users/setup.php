<?php

namespace Mysli\Users;

class Setup
{
    public function before_enable()
    {
        \FS::dir_create(datpath('users'), \FS::EXISTS_MERGE);
        return true;
    }

    public function before_disable()
    {
        return \FS::dir_remove(datpath('users'));
    }
}
