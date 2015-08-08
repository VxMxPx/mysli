<?php

namespace mysli\i18n; class __init
{
    const __use = 'mysli.toolkit.{ fs.fs -> fs, fs.dir -> dir }';

    static function __init()
    {
        if (!dir::exists(fs::tmppath('i18n')))
        {
            return dir::create(fs::tmppath('i18n'));
        }
        return true;
    }
}
