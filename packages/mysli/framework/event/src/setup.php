<?php

namespace mysli\framework\event\setup {

    __use(__namespace__,
        '../fs/file',
        '../json'
    );

    function enable() {
        return fs\dir::create(fs::datpath('event'))
            and json::encode_file(fs::datpath('event/r.json'), []);
    }
    function disable() {
        return fs\dir::remove(fs::datpath('event'));
    }
}
