<?php

namespace Mysli\Token;

class Setup
{
    public function after_enable()
    {
        \Core\FS::dir_create(datpath('mysli.token'));
        \Core\FS::file_create(datpath('mysli.token/registry.json'));
    }
}
