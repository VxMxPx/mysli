<?php

namespace mysli\installer\stub;

function __init() {

    // common.php
    try_to_include(__DIR__.'/common.php');

    // Is cli?
    if (php_sapi_name() === 'cli' || defined('STDIN')) {
        try_to_include(__DIR__.'/../sh/installer.php');
        \mysli\installer\sh\installer\__init($_SERVER['argv']);
    } else {
        try_to_include(__DIR__.'/web.php');
        \mysli\installer\web::boot();
    }
}
function try_to_include($file) {
    if (!file_exists($file)) {
        throw new \Exception("Cannot find: `{$file}`");
    } else {
        include $file;
    }
}

__init();
__HALT_COMPILER();
?>
