<?php

namespace mysli\util\config;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

class __init
{
    static function enable()
    {
        return dir::create(fs::datpath('mysli/util/config'));
    }

    static function disable()
    {
        return dir::remove(fs::datpath('mysli/util/config'));
    }
}
