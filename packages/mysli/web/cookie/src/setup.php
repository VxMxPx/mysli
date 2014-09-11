<?php

namespace mysli\cookie\setup {

    inject::to(__namespace__)->from('mysli/config');

    function enable() {
        return config::select('mysli/cookie')
            ->merge([
                'timeout' => 60 * 60 * 24 * 7, // 7 Days
                'domain'  => null, // dynamic
                'key'     => null, // if provided, cookies will be encrypted
                'prefix'  => ''])
            ->save();
    }
}
