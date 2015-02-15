<?php

namespace mysli\framework\cli\setup;

__use(__namespace__, '
    ./util  AS  cutil
    mysli.framework.fs/fs,file
');

function enable() {
    $dot = file::read(__DIR__ . '/../data/dot.tpl');
    $dot = str_replace(
        '{{PKGPATH}}',
        '/' . fs::relative_path(fs::pkgpath(), fs::datpath()),
         $dot);

    if (file::write(fs::datpath('dot'), $dot)) {
        cutil::execute('cd %s && chmod +x dot', [fs::datpath()]);
        return true;
    } else {
        return false;
    }
}

function disable() {
    return file::remove(fs::datpath('dot'));
}
