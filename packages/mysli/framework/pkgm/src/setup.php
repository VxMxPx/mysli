<?php

namespace mysli\framework\pkgm\setup {

    __use(__namespace__,
        '../fs/{fs,file}'
    );

    function enable() {
        return file::create_recursive(fs::datpath('pkgm/r.json'), true)
            and __use(__namespace__, './pkgm')
            and pkgm::enable('mysli/framework/pkgm', 'installer');
    }
    function disable() {
        return dir::remove(fs::datpath('pkgm'));
    }
}
