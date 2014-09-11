<?php

namespace mysli\framework\config\setup {

    __use(__namespace__,
        '../fs/{fs,dir}'
    );

    function enable() {
        return dir::create(fs::datpath('mysli.config'));
    }
    function disable() {
        return dir::remove(fs::datpath('mysli.config'));
    }
}
