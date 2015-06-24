<?php

namespace mysli\util\tplp;

__use(__namespace__, '
    mysli.framework.event
');

class __init
{
    private static $events = [
        'mysli.dev.pkgc/ignore_list' => 'mysli\util\tplp\services::generate_ignore_list'
    ];

    static function enable()
    {
        event::register(self::$events);
        return true;
    }

    static function disable()
    {
        event::unregister(self::$events);
        return true;
    }
}

