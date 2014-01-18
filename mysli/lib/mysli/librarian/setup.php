<?php

namespace Mysli\Librarian;

class Setup
{
    public function before_enable()
    {
        \FS::dir_create(datpath('librarian'), \FS::EXISTS_MERGE);
        return \JSON::encode_file(datpath('librarian/registry.json'), []);
    }

    public function after_disable()
    {
        return \FS::dir_remove(datpath('librarian'));
    }
}
