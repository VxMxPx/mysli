<?php

namespace mysli\web\ui\setup;

__use(__namespace__, '
    mysli.web.assets
    mysli.util.tplp
    mysli.framework.event
');

function enable()
{
    event::unregister(
        'mysli.web.web:route<*><mwu-developer*>',
        'mysli\\web\\ui::developer'
    );

    return assets::publish('mysli.web.ui');
}
function disable()
{
    event::unregister(
        'mysli.web.web:route<*><mwu-developer*>',
        'mysli\\web\\ui::developer'
    );

    return assets::destroy('mysli.web.ui');
}
