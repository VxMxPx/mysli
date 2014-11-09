<?php

namespace mysli\external\zepto\setup;

__use(__namespace__, '
    mysli/web/assets
');

function enable() {
    return assets::publish('mysli/external/zepto');
}
function disable() {
    return assets::destroy('mysli/external/zepto');
}
