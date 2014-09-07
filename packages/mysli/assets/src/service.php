<?php

namespace mysli\assets {

    \inject::to(__namespace__)
    ->from('mysli/tplp');

    class service {
        /**
         * Register template's global functions.
         * @return null
         */
        static function register() {
            tplp::register_function('css', function ($list) {
                return assets::tags('css', $list);
            });
            tplp::register_function('javascript', function ($list) {
                return assets::tags('js', $list);
            });
        }
    }
}
