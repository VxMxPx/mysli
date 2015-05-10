<?php

namespace mysli\web\token;

__use(__namespace__, '
    mysli.framework.fs/fs,file,dir
');

class __init
{
    static function __init()
    {
        token::set_data_path(fs::datpath('mysli/web/token'));
        token::reload();
    }

    static function enable()
    {
        return dir::create(fs::datpath('mysli/web/token')) &&
            file::write(fs::datpath('mysli/web/token/r.json'), '[]');
    }

    static function disable()
    {
        return dir::remove(fs::datpath('mysli/web/token'));
    }
}
