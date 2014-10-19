<?php

namespace mysli\web\zepto\setup;

__use(__namespace__, 'mysli/web/assets');

function enable() {
    return assets::publish('mysli/web/zepto');
}
function disable() {
    return assets::destroy('mysli/web/zepto');
}
