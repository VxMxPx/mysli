<?php

namespace mysli\dev\phpt;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

class __init
{
    static function enable()
    {
        return dir::create(fs::tmppath('phpt'));
    }

    static function disable()
    {
        return dir::remove(fs::tmppath('phpt'));
    }
}
