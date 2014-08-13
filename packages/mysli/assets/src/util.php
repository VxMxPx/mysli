<?php

namespace mysli\assets {

    use mysli\fs\file as file;
    use mysli\config as config;
    use mysli\arr as arr;
    use mysli\str as str;

    trait util {
        /**
         * Get parsed ext, e.g.: stly => css
         * @param  string $file
         * @return string
         */
        private static function parse_extention($file) {
            $ext  = file::extension($file);
            $list = config::select('mysli/assets', 'ext', []);
            if (arr::has($ext, $list)) {
                return str::sub($file, 0, -(strlen($ext))) . $list[$ext];
            } else {
                return $file;
            }
        }
    }
}
