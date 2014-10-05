<?php

namespace mysli\util\tplp\setup;

__use(__namespace__,
    'mysli/framework/fs/{fs,file,dir}'
);

const basedir = 'mysli/util/tplp';

function enable() {
    return dir::create(fs::datpath(basedir, 'cache'));
}
function disable() {
    return dir::remove(fs::datpath(basedir));
}
