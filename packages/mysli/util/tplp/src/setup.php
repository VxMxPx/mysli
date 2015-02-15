<?php

namespace mysli\util\tplp\setup;

__use(__namespace__, '
    mysli.util.config
    mysli.framework.fs/fs,file,dir
');

const basedir = 'mysli/util/tplp';

function enable() {
    $c = config::select('mysli/util/tplp');
    $c->merge(['debug' => false]);
    return dir::create(fs::datpath(basedir, 'cache'))
        && $c->save();
}
function disable() {
    return dir::remove(fs::datpath(basedir))
        && config::select('mysli/util/tplp')->destroy();
}
