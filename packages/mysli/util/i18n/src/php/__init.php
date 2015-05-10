<?php

namespace mysli\util\i18n;

__use(__namespace__, '
    ./i18n
    mysli.framework.event
    mysli.framework.fs/fs,dir
    mysli.util.config
');

class __init
{
    protected $events = [
        'mysli.dev.pkgc/ignore_list' => 'mysli\util\i18n\services::generate_ignore_list'
    ];

    static function __init()
    {
        # Set default languages as set in config
        i18n::set_default_language(
            config::select('mysli.util.i18n', 'primary_language', 'en'),
            config::select('mysli.util.i18n', 'secondary_language')
        );
    }

    static function enable()
    {
        event::register(self::$events);

        $c = config::select('mysli.util.i18n');
        $c->merge([
            'primary_language' => 'en',
            'secondary_language' => null
        ]);

        return $c->save();
    }

    static function disable()
    {
        event::unregister(self::$events);
        return config::select('mysli.util.i18n')->destroy();
    }
}
