<?php

namespace mysli\web\token\setup;

__use(__namespace__, '
    mysli.framework.fs/fs,file,dir
');

function enable()
{
    return dir::create(fs::datpath('mysli/web/token')) &&
        file::write(fs::datpath('mysli/web/token/r.json'), '[]');
}

function disable()
{
    return dir::remove(fs::datpath('mysli/web/token'));
}
