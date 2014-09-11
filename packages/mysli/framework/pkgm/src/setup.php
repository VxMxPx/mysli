<?php

namespace mysli\framework\pkgm\setup {

    __use(__namespace__,
        './pkgm',
        '../fs'
    );

    function enable() {
        return fs\file::create_recursive(fs::datpath('pkgm/r.json'))
            and pkgm::enable('mysli/pkgm', 'self');
    }
    function disable() {
        return fs\dir::remove(fs::datpath('pkgm'));
    }
}
