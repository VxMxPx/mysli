<?php

namespace Mysli\Users;

class Setup
{
    protected $core;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core = $dependencies['core'];
    }

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
