<?php

namespace mysli\dev\pkgc\setup;

__use(__namespace__, '
    mysli/framework/fs/{fs,dir}
');

function enable() {
    return dir::create(fs::tmppath('pkgc'));
}
function disable() {
    if (dir::exists(fs::tmppath('pkgc'))) {
        return dir::remove(fs::tmppath('pkgc'));
    } else return true;
}
