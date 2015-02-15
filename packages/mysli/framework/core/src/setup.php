<?php

namespace mysli\framework\core\setup;

function enable($pkgpath, $datpath) {

    $pkgpath = rtrim($pkgpath, '\\/');
    $datpath = rtrim($datpath, '\\/');
    $tmppath = $datpath.'/temp';
    // Get self path
    $selfrelease = __FILE__;
    if (substr($selfrelease, -5) === '.phar') {
        $selfrelease = basename($selfrelease);
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
        $datpath.'/boot/core.json',
        json_encode([
            'core' => [
                'package' => 'mysli.framework.core',
            ],
            'autoloader' => [
                'package' => 'mysli.framework.core',
                'file'    => 'autoloader',
                'call'    => 'load'
            ]
        ])
    ) && (bool) file_put_contents(
        $datpath.'/boot/packages.json',
        json_encode([
            'mysli.framework.core' => $selfrelease
        ])
    );
}
