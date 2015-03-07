<?php

namespace mysli\web\assets\setup;

__use(__namespace__, '
    mysli.framework.event
    mysli.util.config
');

function enable()
{
    event::register(
        'mysli.dev.pkgc/ignore_list',
        'mysli\web\assets\services::generate_ignore_list'
    );
    event::register(
        'mysli.dev.pkgc/done',
        'mysli\web\assets\services::map_to_phar'
    );

    $c = config::select('mysli.web.assets');
    $c->merge(['debug' => false]);
    return $c->save();
}
function disable()
{
    event::unregister(
        'mysli.dev.pkgc/ignore_list',
        'mysli\web\assets\services::generate_ignore_list'
    );
    event::unregister(
        'mysli.dev.pkgc/done',
        'mysli\web\assets\services::map_to_phar'
    );

    return config::select('mysli.web.assets')->destroy();
}
