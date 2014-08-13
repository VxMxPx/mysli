<?php

namespace mysli\config {

    use mysli\fs as fs;
    use mysli\fs\dir as dir;

    class setup {
        static function enable() {
            return dir::create(fs::datpath('mysli.config'), dir::exists_merge);
        }
        static function cleanup() {
            return dir::remove(fs::datpath('mysli.config'));
        }
    }
}
