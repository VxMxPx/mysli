<?php

namespace mysli\tplp; class __setup
{
    const __use = '
        mysli.toolkit.fs.{ fs, dir }
    ';

    static function enable()
    {
        return dir::create(fs::tmppath('tplp'));
    }

    static function disable()
    {
        dir::remove(fs::tmppath('tplp'));
        return true;
    }
}
