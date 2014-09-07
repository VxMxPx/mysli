<?php

namespace mysli\pkgm\setup {

    use mysli\pkgm\pkgm as pkgm;

    \inject::to(__namespace__)
    ->from('mysli/fs');

    function enable() {
        return fs\file::create_recursive(fs::datpath('pkgm/r.json'))
            and pkgm::enable('mysli/pkgm', 'self');
    }

    function disable() {
        return fs\dir::remove(fs::datpath('pkgm'));
    }
}
