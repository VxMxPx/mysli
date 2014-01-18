<?php

namespace Mysli\Event;

class Setup
{
    public function before_enable()
    {
        \FS::dir_create(datpath('event'), \FS::EXISTS_MERGE);
        return \JSON::encode_file(datpath('event/registry.json'), []);
    }

    public function after_disable()
    {
        return \FS::dir_remove(datpath('event'));
    }
}
