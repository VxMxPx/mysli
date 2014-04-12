<?php

namespace Mysli\Tplp;

class Setup
{
    public function before_enable()
    {
        \Core\FS::dir_create(datpath('mysli.tplp'), \Core\FS::EXISTS_MERGE);
        return true;
    }
}
