<?php

namespace Mysli\Config;

class Setup
{
    public function before_enable()
    {
        return \Core\FS::dir_create(datpath('mysli.config'), \Core\FS::EXISTS_MERGE);
    }
}
