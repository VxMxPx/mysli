<?php

namespace mysli\util\i18n; class __setup
{
    const __use = '
        .i18n
        mysli.toolkit.{event,config}
        mysli.toolkit..fs.{fs,dir}
    ';

    protected $events = [
        'mysli.dev.pkgc:ignore_list' => 'mysli.i18n:generate_ignore_list'
    ];

    static function enable()
    {
        event::register(self::$events);

        $c = config::select('mysli.i18n');
        $c->merge([
            'primary_language' => 'en',
            'secondary_language' => null
        ]);

        return $c->save();
    }

    static function disable()
    {
        event::unregister(self::$events);
        return config::select('mysli.i18n')->destroy();
    }
}
