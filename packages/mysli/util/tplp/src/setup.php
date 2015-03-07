<?php

namespace mysli\util\tplp\setup;

__use(__namespace__, '
    mysli.framework.event
');

function enable()
{
    event::register(
        'mysli.dev.pkgc/ignore_list',
        'mysli\util\tplp\services::generate_ignore_list'
    );
    return true;
}

function disable()
{
    event::unregister(
        'mysli.dev.pkgc/ignore_list',
        'mysli\util\tplp\services::generate_ignore_list'
    );
    return true;
}
