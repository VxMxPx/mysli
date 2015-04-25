<?php

namespace mysli\js\ui\setup;

__use(__namespace__, '
    mysli.web.assets
    mysli.util.tplp
    mysli.framework.event
');

function enable()
{
    event::unregister(
        'mysli.web.web:route<*><mwu-developer*>',
        'mysli\\js\\ui::developer'
    );

    return assets::publish('mysli.js.ui');
}
function disable()
{
    event::unregister(
        'mysli.web.web:route<*><mwu-developer*>',
        'mysli\\js\\ui::developer'
    );

    return assets::destroy('mysli.js.ui');
}
