<?php

namespace mysli\web\ui\setup;

use mysli\web\assets;
use mysli\util\tplp;
use mysli\framework\event;

function enable() {
    return assets::publish('mysli/web/ui');
}
function disable() {
    tplp::remove_cache('mysli/web/ui');
    event::unregister('mysli/web/web:route<*><mysli-ui-examples*>',
                        'mysli\\web\\ui::examples');
    return assets::destroy('mysli/web/ui');
}
