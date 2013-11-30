<?php

namespace Mysli\Dot;

class Setup
{
    protected $core;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core = $dependencies['core'];
    }

    public function before_enable()
    {
        // Load dot.tpl
        $dot_contents = file_get_contents(libpath('mysli/dot/setup/dot.tpl'));
        // Replace {{LIBPATH}} and {{PUBPATH}}
        $ds = DIRECTORY_SEPARATOR;
        $dot_contents = str_replace(
            [
                '{{LIBPATH}}',
                '{{PUBPATH}}'
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(libpath(), datpath())),
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(pubpath(), datpath())),
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
        \FS::file_remove(datpath('dot'));
        return true;
    }
}
