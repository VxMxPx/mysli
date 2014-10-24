<?php

namespace mysli\web\ui\setup;

__use(__namespace__, '
    mysli/web/assets
    mysli/util/tplp
    mysli/framework/event
');

function enable() {
    return assets::publish('mysli/web/ui');
}

function disable() {
    tplp::remove_cache('mysli/web/ui');
    event::unregister('mysli/web/web:route<*><mysli-ui-examples*>',
                        'mysli\\web\\ui::examples');
    return assets::destroy('mysli/web/ui');
}
