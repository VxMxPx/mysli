<?php

namespace mysli\util\config\setup;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

function enable() {
    return dir::create(fs::datpath('mysli/util/config'));
}

function disable() {
    return dir::remove(fs::datpath('mysli/util/config'));
}
