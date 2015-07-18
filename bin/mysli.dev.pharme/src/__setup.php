<?php

namespace mysli\dev\pkgc; class __setup
{
    const __use = '
        mysli.toolkit.fs.{fs,dir}
    ';

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
