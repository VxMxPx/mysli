<?php

namespace mysli\frontend; class __setup
{
    const __use = 'mysli.toolkit.{
        config,
        fs.fs -> fs,
        fs.dir -> dir
    }';

    static function enable()
    {
        return dir::create(fs::cntpath('themes'));
    }

    static function disable()
    {
        return dir::remove(fs::cntpath('themes'));
    }
}
