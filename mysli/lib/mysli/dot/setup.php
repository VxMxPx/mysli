<?php

namespace Mysli\Dot;

class Setup
{
    public function before_enable()
    {
        // Load dot.tpl
        $dot_contents = file_get_contents(libpath('mysli/dot/setup/dot.tpl'));
        // Replace {{LIBPATH}} and {{PUBPATH}}
        $dot_contents = str_replace(
            [
                '{{LIBPATH}}'
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(libpath(), datpath()))
            ],
            $dot_contents
        );
        // Create index.php
        file_put_contents(datpath('dot'), $dot_contents);

        return true;
    }

    public function after_enable()
    { return true; }

    public function before_disable()
    { return true; }

    public function after_disable()
    {
        \Core\FS::file_remove(datpath('dot'));
        return true;
    }
}
