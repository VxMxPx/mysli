<?php

namespace mysli\users; class __setup
{
    const __use = '
        mysli.toolkit.fs.{ fs, dir }
    ';

    static function enable()
    {
        return dir::create(fs::cntpath('users'));
    }

    static function cleanup()
    {
        return dir::remove(fs::cntpath('users'));
    }
}
