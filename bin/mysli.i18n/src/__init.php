<?php

namespace mysli\i18n; class __init
{
    const __use = '
        .i18n
        mysli.toolkit.config
    ';

    static function __init()
    {
        # Set default languages as set in config
        i18n::set_default_language(
            config::select('mysli.i18n', 'primary_language', 'en'),
            config::select('mysli.i18n', 'secondary_language')
        );
    }
}
