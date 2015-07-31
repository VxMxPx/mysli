<?php

namespace mysli\tplp; class __init
{
    const __use = 'mysli.toolkit.{ fs.fs -> fs, fs.dir -> dir }';

    static function __init()
    {
        if (!dir::exists(fs::tmppath('tplp')))
        {
            return dir::create(fs::tmppath('tplp'));
        }
        return true;
    }
}
