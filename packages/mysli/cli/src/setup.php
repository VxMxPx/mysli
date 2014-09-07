<?php

namespace mysli\cli\setup {

    use \mysli\cli\util as cutil;
    use \mysli\fs as fs;

    function enable() {
        $dot = fs\file::read(__DIR__ . '/../data/dot.tpl');
        $dot = str_replace(
            '{{PKGPATH}}',
            '/' . fs::relative_path(fs::pkgpath(), fs::datpath()),
             $dot);
        fs\file::write(fs::datpath('dot'), $dot);
        cutil::execute('cd %s && chmod +x dot', fs::datpath());
        return true;
    }

    function disable() {
        return fs\dir::remove(fs::datpath('dot'));
    }
}
