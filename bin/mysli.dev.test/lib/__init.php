<?php

namespace mysli\dev\test; class __init
{
    const __use = 'mysli.toolkit.{ fs.fs -> fs, fs.dir -> dir }';

    static function __init()
    {
        if (!dir::exists(fs::tmppath('dev.test')))
        {
            return dir::create(fs::tmppath('dev.test'));
        }
        return true;
    }
}
