<?php

namespace Mysli\Config;

class Setup
{
    public function before_enable()
    {
        return \Core\FS::dir_create(datpath('config'), \Core\FS::EXISTS_MERGE);
    }

    public function before_disable()
    {
        return \Core\FS::dir_remove(datpath('config'));
    }
}
