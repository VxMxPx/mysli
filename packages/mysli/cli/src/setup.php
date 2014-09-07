<?php

namespace mysli\cli\setup {

    \inject::to(__namespace__)
    ->from('mysli/cli/util', 'cutil')
    ->from('mysli/fs');

    function enable() {
        $dot = fs\file::read(__DIR__ . '/../data/dot.tpl');
        $dot = str_replace(
            '{{PKGPATH}}',
            '/' . fs::relative_path(fs::pkgpath(), fs::datpath()),
             $dot);

        return fs\file::write(fs::datpath('dot'), $dot)
            and (bool) cutil::execute('cd %s && chmod +x dot', fs::datpath());
    }

    function disable() {
        return fs\file::remove(fs::datpath('dot'));
    }
}
