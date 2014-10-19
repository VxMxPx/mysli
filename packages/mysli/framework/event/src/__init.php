<?php

namespace mysli\framework\event {

    __use(__namespace__,
        '../fs'
    );

    function __init() {
        event::__init(fs::datpath('mysli/framework/event/r.json'));
    }
}
