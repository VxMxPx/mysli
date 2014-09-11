<?php

namespace Mysli\Event;

class Setup
{
    public function before_enable()
    {
        \Core\FS::dir_create(datpath('event'), \Core\FS::EXISTS_MERGE);
        return \Core\JSON::encode_file(datpath('event/registry.json'), []);
    }

    public function after_disable()
    {
        return \Core\FS::dir_remove(datpath('event'));
    }
}
