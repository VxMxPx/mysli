<?php

namespace mysli\framework\event\setup;

__use(__namespace__,
    '../fs/{fs,dir}',
    '../json'
);

function enable() {
    return dir::create(fs::datpath('mysli/framework/event'))
        && json::encode_file(fs::datpath('mysli/framework/event/r.json'), []);
}
function disable() {
    return dir::remove(fs::datpath('mysli/framework/event'));
}
