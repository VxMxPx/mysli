<?php

namespace mysli\framework\core\setup;

function enable($pkgpath, $datpath) {

    $pkgpath = rtrim($pkgpath, '\\/');
    $datpath = rtrim($datpath, '\\/');
    $tmppath = $datpath.'/temp';
    // Get self path
    $selfrelease = __FILE__;
    if (substr($selfrelease, -5) === '.phar') {
        $selfrelease = substr(basename($selfrelease), 0, -5);
    } else {
        $selfrelease = 'mysli/framework/core';
    }


    // Create DATA directory
    if (!is_dir($datpath)) {
        if (!mkdir($datpath, 0777, true)) {
            throw new \Exception('Cannot create `data` directory!', 2);
        }
    }

    // Create boot directory
    if (!is_dir($datpath . '/boot')) {
        if (!mkdir($datpath . '/boot')) {
            throw new \Exception('Cannot create `boot` directory.', 3);
        }
    }

    // Crete TEMP directory
    if (!is_dir($tmppath)) {
        if (!mkdir($tmppath)) {
            throw new \Exception('Cannot create `temp` directory.', 4);
        }
    }

    // Writte boot file
    return (bool) file_put_contents(
        $datpath.'/boot/r.json',
        json_encode([
            "boot" => [
                'core' => 'mysli.framework.core',
                'autoloader' => 'mysli.framework.core/autoloader:load',
                'pkg' => 'mysli.framework.core/pkg'
            ],
            "pkg" => [
                'mysli.framework.core' => [
                    'package' => 'mysli.framework.core',
                    'release' => $selfrelease
                ]
            ]
        ])
    );
}
