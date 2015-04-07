<?php

namespace mysli\web\users\setup;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

function enable()
{
    return dir::create(fs::datpath('mysli/web/users'));
}

function disable()
{
    return dir::remove(fs::datpath('mysli/web/users'));
}
