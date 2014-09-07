<?php

namespace mysli\assets {

    \inject::to(__namespace__)
    ->from('mysli/fs/file')
    ->from('mysli/config')
    ->from('mysli/core/type/{str,arr}');

    trait util {
        /**
         * Get parsed ext, e.g.: stly => css
         * @param  string $file
         * @return string
         */
        private static function parse_extention($file) {
            $ext  = file::extension($file);
            $list = config::select('mysli/assets', 'ext', []);
            if (arr::key_in($list, $ext)) {
                return str::sub($file, 0, -(strlen($ext))) . $list[$ext];
            } else {
                return $file;
            }
        }
    }
}
