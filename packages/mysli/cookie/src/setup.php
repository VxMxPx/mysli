<?php

namespace mysli\cookie {

    use mysli\config as config;

    class setup {
        function enable() {
            return config::select('mysli/cookie')
                    ->merge([
                        'timeout' => 60 * 60 * 24 * 7, // 7 Days
                        'domain'  => '', // Dynamic
                        'prefix'  => 'mysli_'])
                    ->save();
        }
    }
}
