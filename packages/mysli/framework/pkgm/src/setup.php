<?php

namespace mysli\framework\pkgm\setup;

__use(__namespace__, '
    mysli.framework.fs/fs,file,dir
');

function enable() {

    $selfrelease = __FILE__;
    if (substr($selfrelease, -5) === '.phar') {
        $selfrelease = basename($selfrelease);
    } else {
        $selfrelease = 'mysli/framework/pkgm';
    }

    return file::create_recursive(
        fs::datpath('mysli/framework/pkgm/r.json'), true) &&
        __use(__namespace__, './pkgm') &&
        pkgm::enable($selfrelease, 'installer');
}
function disable() {
    return dir::remove(fs::datpath('mysli/framework/pkgm'));
}
