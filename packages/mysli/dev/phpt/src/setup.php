<?php

namespace mysli\dev\phpt\setup;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
');

function enable()
{
    return dir::create(fs::tmppath('phpt'));
}
function disable()
{
    return dir::remove(fs::tmppath('phpt'));
}
