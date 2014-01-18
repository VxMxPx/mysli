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
        return true;
    }

    public function after_disable()
    {
        return \FS::dir_remove(datpath('librarian'));
    }
}
