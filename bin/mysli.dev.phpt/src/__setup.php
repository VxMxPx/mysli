<?php

namespace mysli\dev\phpt; class __setup
{
    const __use = '
        mysli.toolkit.fs.{fs,dir}
    ';
    
    static function enable()
    {
        return dir::create(fs::tmppath('phpt'));
    }

    static function disable()
    {
        return dir::remove(fs::tmppath('phpt'));
    }
}
