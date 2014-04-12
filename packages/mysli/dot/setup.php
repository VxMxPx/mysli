<?php

namespace Mysli\Dot;

class Setup
{
    public function before_enable()
    {
        // Load dot.tpl
        $dot_contents = file_get_contents(pkgpath('mysli/dot/setup/dot.tpl'));
        // Replace {{PKGPATH}}
        $dot_contents = str_replace(
            [
                '{{PKGPATH}}'
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(pkgpath(), datpath()))
            ],
            $dot_contents
        );
        // Create index.php
        file_put_contents(datpath('dot'), $dot_contents);
        system('cd ' . datpath() . ' && chmod +x dot');

        return true;
    }

    public function after_disable()
    {
        \Core\FS::file_remove(datpath('dot'));
        return true;
    }
}
