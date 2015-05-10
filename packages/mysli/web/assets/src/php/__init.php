<?php

namespace mysli\web\assets;

__use(__namespace__, '
    mysli.framework.event
    mysli.util.config
');

class __init
{
    private static $events = [
        'mysli.dev.pkgc/ignore_list' => 'mysli\web\assets\services::generate_ignore_list',
        'mysli.dev.pkgc/done'        => 'mysli\web\assets\services::map_to_phar'
    ];

    static function enable()
    {
        event::register(self::$events);
        $c = config::select('mysli.web.assets');
        $c->merge(['debug' => false]);
        return $c->save();
    }

    static function disable()
    {
        event::unregister(self::$events);
        return config::select('mysli.web.assets')->destroy();
    }
}
