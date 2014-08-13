<?php

namespace mysli\core {
    class setup {
        static function enable($pkgpath, $datpath) {

            $pkgpath = rtrim($pkgpath, '\\/');
            $datpath = rtrim($datpath, '\\/');

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

            file_put_contents(
                $datpath . '/core/id.json',
                json_encode([
                    'file'  => 'mysli/core/src/core.php',
                    'class' => 'mysli\\core\\core',
                ])
            );

            return true;
        }
    }
}
