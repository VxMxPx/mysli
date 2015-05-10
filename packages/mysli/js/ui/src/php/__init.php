<?php

namespace mysli\js\ui;

__use(__namespace__, '
    mysli.web.assets
    mysli.util.tplp
    mysli.framework.event
');

class __init
{
    static $events = [
        'mysli.web.web:route<*><mwu-developer*>' => 'mysli\\js\\ui::developer'
    ];

    static function enable()
    {
        // Yes, unregister, true registration goes through cli
        event::unregister(self::$events);
        return assets::publish('mysli.js.ui');
    }

    static function disable()
    {
        event::unregister(self::$events);
        return assets::destroy('mysli.js.ui');
    }
}
