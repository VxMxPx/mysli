<?php

namespace mysli\dev\pkgc;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

class __init
{
    static function enable()
    {
        return dir::create(fs::tmppath('pkgc'));
    }
    
    static function disable()
    {
        if (dir::exists(fs::tmppath('pkgc')))
        {
            return dir::remove(fs::tmppath('pkgc'));
        }
        else
        {
            return true;
        }
    }
}
