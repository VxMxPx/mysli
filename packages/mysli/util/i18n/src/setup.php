<?php

namespace mysli\util\i18n\setup;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
    mysli.util.config
');

function enable()
{
    $c = config::select('mysli.util.i18n');
    $c->merge([
        'primary_language' => 'en',
        'secondary_language' => null
    ]);

    return $c->save();
}

function disable()
{
    return config::select('mysli.util.i18n')->destroy();
}
