<?php

namespace Mysli\Librarian;

class Setup
{
    public function before_enable()
    {
        \Core\FS::dir_create(datpath('librarian'), \Core\FS::EXISTS_MERGE);
        \Core\JSON::encode_file(datpath('librarian/id.json'), [
            'file'  => 'mysli/librarian/librarian.php',
            'class' => 'Mysli\\Librarian',
        ]);
        \Core\JSON::encode_file(datpath('librarian/registry.json'), []);

        // Add self to libraries oO
        if (!class_exists('Mysli\\Librarian', false)) {
            include libpath('mysli/librarian/librarian.php');
        }
        $librarian = new \Mysli\Librarian();
        $librarian->enable('mysli/librarian');
        return true;
    }

    public function after_disable()
    {
        return \Core\FS::dir_remove(datpath('librarian'));
    }
}
