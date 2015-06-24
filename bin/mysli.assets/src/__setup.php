<?php

namespace mysli\assets; class __setup
{
    const __use = '
        mysli.toolkit.{event,config}
    ';

    private static $events = [
        'mysli.dev.pkgc:ignore_list' => 'mysli.assets:generate_ignore_list',
        'mysli.dev.pkgc:done'        => 'mysli.assets:map_to_phar'
    ];

    static function enable()
    {
        event::register(self::$events);
        $c = config::select('mysli.assets');
        $c->merge(['debug' => false]);
        return $c->save();
    }

    static function disable()
    {
        event::unregister(self::$events);
        return config::select('mysli.assets')->destroy();
    }
}
