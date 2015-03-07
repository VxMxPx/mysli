<?php

namespace mysli\util\i18n\setup;

__use(__namespace__, '
    mysli.framework.event
    mysli.framework.fs/fs,dir
    mysli.util.config
');

function enable()
{
    event::register(
        'mysli.dev.pkgc/ignore_list',
        'mysli\util\i18n\services::generate_ignore_list'
    );

    $c = config::select('mysli.util.i18n');
    $c->merge([
        'primary_language' => 'en',
        'secondary_language' => null
    ]);

    return $c->save();
}

function disable()
{
    event::unregister(
        'mysli.dev.pkgc/ignore_list',
        'mysli\util\i18n\services::generate_ignore_list'
    );
    return config::select('mysli.util.i18n')->destroy();
}
