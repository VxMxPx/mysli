<?php

namespace mysli\framework\cli;

__use(__namespace__, '
    mysli.framework.fs/fs,file
');

class __init
{
    static function enable()
    {
        $dot = file::read(__DIR__.'/../../data/dot.tpl');

        $dot = str_replace(
            '{{PKGPATH}}',
            '/' . fs::relative_path(fs::pkgpath(), fs::datpath()),
            $dot
        );

        if (file::write(fs::datpath('dot'), $dot))
        {
            exec(vsprintf('cd %s && chmod +x dot', [fs::datpath()]));
            return true;
        }
        else
        {
            return false;
        }
    }

    static function disable()
    {
        return file::remove(fs::datpath('dot'));
    }
}
