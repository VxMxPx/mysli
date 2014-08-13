<?php

namespace mysli\cli {

    use \mysli\cli\util as cutil;
    use \mysli\type\str as str;
    use \mysli\fs\file as file;
    use \mysli\fs as fs;

    class setup {
        static function enable() {
            $dot = file::read(__DIR__ . '/../data/dot.tpl');
            $dot = str::replace(
                '{{PKGPATH}}',
                '/' . fs::relative_path(fs::pkgpath(), fs::datpath()),
                 $dot);
            file::write(fs::datpath('dot'), $dot);
            cutil::execute('cd %s && chmod +x dot', fs::datpath());
            return true;
        }
        static function disable() {
            return fs::remove(fs::datpath('dot'));
        }
    }
}
