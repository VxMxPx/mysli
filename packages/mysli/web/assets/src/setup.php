<?php

namespace mysli\web\assets\setup;

__use(__namespace__,
    'mysli.util.config'
);

function enable() {
    $c = config::select('mysli/web/assets');
    $c->merge(['debug' => false]);
    return $c->save();
}
function disable() {
    return config::select('mysli/web/assets')->destroy();
}
