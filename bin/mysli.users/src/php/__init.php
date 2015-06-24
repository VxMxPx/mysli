<?php

namespace mysli\web\users;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

class __init
{
    static function enable()
    {
        return dir::create(fs::datpath('mysli/web/users'));
    }

    static function disable()
    {
        return dir::remove(fs::datpath('mysli/web/users'));
    }
}
