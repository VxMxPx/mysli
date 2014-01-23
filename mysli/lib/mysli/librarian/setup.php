<?php

namespace Mysli\Librarian;

class Setup
{
    public function before_enable()
    {
        \FS::dir_create(datpath('librarian'), \FS::EXISTS_MERGE);
        \JSON::encode_file(datpath('librarian/id.json'), [
            'file'  => 'mysli/librarian/librarian.php',
            'class' => 'Mysli\\Librarian',
        ]);
        \JSON::encode_file(datpath('librarian/registry.json'), []);

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
        return \FS::dir_remove(datpath('librarian'));
    }
}
