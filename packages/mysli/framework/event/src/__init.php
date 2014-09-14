<?php

namespace mysli\framework\event {

    __use(__namespace__,
        '../fs'
    );

    function __init() {
        event::set_datasource(fs::datpath('event/r.json'));
        event::reload();
    }
}
