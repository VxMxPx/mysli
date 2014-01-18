<?php

namespace Mysli\Config;

class Setup
{
    public function before_enable()
    {
        return \FS::dir_create(datpath('config'), \FS::EXISTS_MERGE);
    }

    public function before_disable()
    {
        return \FS::dir_remove(datpath('config'));
    }
}
