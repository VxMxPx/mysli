<?php

namespace mysli\config\setup {

    \inject::to(__namespace__)
    ->from('mysli/fs/{fs,dir}');

    function enable() {
        return dir::create(fs::datpath('mysli.config'));
    }
    function disable() {
        return dir::remove(fs::datpath('mysli.config'));
    }
}
