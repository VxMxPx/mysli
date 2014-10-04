<?php

namespace mysli\util\i18n\setup;

__use(__namespace__,
    'mysli/framework/fs/{fs,dir}',
    '../config'
);

function enable() {
    $c = config::select('mysli/util/i18n');
    $c->merge([
        'primary_language' => 'en',
        'secondary_language' => null
    ]);
    return
        dir::create(fs::datpath('mysli/util/i18n')) and
        $c->save();
}

function disable() {
    return dir::remove(fs::datpath('mysli/util/i18n')) and
           config::select('mysli/util/i18n')->destroy();
}
