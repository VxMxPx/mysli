<?php

namespace mysli\installer\stub;

function __init()
{
    $base_dir = __FILE__;
    if (substr($base_dir, -5) === '.phar')
    {
        $base_dir = 'phar://'.$base_dir;
    }
    else
    {
        // ROOT!
        $base_dir = dirname(dirname($base_dir));
    }

    // common.php
    try_to_include($base_dir.'/src/php/common.php');

    // Is cli?
    if (php_sapi_name() === 'cli' || defined('STDIN'))
    {
        try_to_include($base_dir.'/sh/installer.php');
        \mysli\installer\sh\installer\__init($_SERVER['argv']);
    }
    else
    {
        try_to_include($base_dir.'/src/php/web.php');
        \mysli\installer\web::boot();
    }
}

function try_to_include($file)
{
    if (!file_exists($file))
    {
        throw new \Exception("Cannot find: `{$file}`");
    }
    else
    {
        include $file;
    }
}

__init();
__HALT_COMPILER();
?>
