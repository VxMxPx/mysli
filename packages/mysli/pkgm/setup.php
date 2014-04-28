<?php

namespace Mysli\Pkgm;

class Setup
{
    public function before_enable()
    {
        \Core\FS::dir_create(datpath('pkgm'), \Core\FS::EXISTS_MERGE);
        \Core\JSON::encode_file(datpath('pkgm/id.json'), [
            'file'  => 'mysli/pkgm/pkgm.php',
            'class' => 'Mysli\\Pkgm\\Pkgm',
        ]);
        \Core\JSON::encode_file(datpath('pkgm/registry.json'), ['enabled' => [], 'roles' => []]);

        // Add self to packages list oO
        if (!class_exists('Mysli\\Pkgm\\Pkgm', false)) {
            include pkgpath('mysli/pkgm/pkgm.php');
        }
        return true;
    }

    public function after_disable()
    {
        return \Core\FS::dir_remove(datpath('pkgm'));
    }
}
