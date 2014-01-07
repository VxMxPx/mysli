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
        $destination = datpath('users');
        \FS::dir_create($destination, \FS::EXISTS_MERGE);
        // Create new file if not already there...
        \FS::file_create(ds($destination, 'users.json'));

        return true;
    }

    public function before_disable()
    {
        // Clean up!
        return \FS::dir_remove(datpath('users'));
    }
}
