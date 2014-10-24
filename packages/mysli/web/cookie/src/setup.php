<?php

namespace mysli\web\cookie\setup;

__use(__namespace__, '
    mysli/util/config
');

function enable() {
    $c = config::select('mysli/web/cookie');
    $c->merge([
        'timeout' => 60 * 60 * 24 * 7, // 7 Days
        'domain'  => null, // automatically acquired
        'key'     => null, // TODO: if provided, cookies will be encrypted
        'prefix'  => '']);
    return $c->save();
}
function disable() {
    return config::select('mysli/web/cookies')->destroy();
}
