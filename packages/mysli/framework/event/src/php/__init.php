<?php

namespace mysli\framework\event;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
    mysli.framework.json
');

class __init
{
    static function __init()
    {
        event::__init(fs::datpath('mysli/framework/event/r.json'));
    }

    static function enable()
    {
        return dir::create(fs::datpath('mysli/framework/event'))
            && json::encode_file(fs::datpath('mysli/framework/event/r.json'), []);
    }

    static function disable()
    {
        return dir::remove(fs::datpath('mysli/framework/event'));
    }
}
