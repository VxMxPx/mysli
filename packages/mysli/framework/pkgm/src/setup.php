<?php

namespace mysli\framework\pkgm\setup;

__use(__namespace__, '
    mysli/framework/fs/{fs,file,dir}
');

function enable() {
    return file::create_recursive(fs::datpath('pkgm/r.json'), true)
        && __use(__namespace__, './pkgm')
        && pkgm::enable('mysli/framework/pkgm', 'installer');
}
function disable() {
    return dir::remove(fs::datpath('pkgm'));
}
