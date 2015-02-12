<?php

namespace mysli\framework\core\setup;

function enable($pkgpath, $datpath) {
    $pkgpath = rtrim($pkgpath, '\\/');
    $datpath = rtrim($datpath, '\\/');
    $tmppath = $datpath.DIRECTORY_SEPARATOR.'temp';

    if (!is_dir($datpath)) {
        if (!mkdir($datpath, 0777, true)) {
            throw new \Exception('Cannot create data directory!', 2);
        }
    }

    if (!is_dir($datpath . '/core')) {
        if (!mkdir($datpath . '/core')) {
            throw new \Exception('Cannot create `core` directory.', 3);
        }
    }

    if (!is_dir($tmppath)) {
        if (!mkdir($tmppath)) {
            throw new \Exception('Cannot create `temp` directory.', 4);
        }
    }

    return (bool) file_put_contents(
        $datpath . '/core/id.json',
        json_encode([
            'package' => 'mysli.framework.core',
            'enabled' => gmdate('YmdHis')
        ])
    );
}
