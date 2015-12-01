<?php

namespace mysli\token; class __init
{
    const __use = '
        mysli.toolkit.fs.{ fs, dir, file }
    ';

    static function enable()
    {
        return dir::create(fs::cntpath('tokens'));
    }

    static function cleanup()
    {
        return dir::remove(fs::cntpath('tokens'));
    }
}
