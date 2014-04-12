<?php

namespace Mysli\Users;

class Setup
{
    public function before_enable()
    {
        return \Core\FS::dir_create(datpath('mysli.users'), \Core\FS::EXISTS_MERGE);
    }
}
