<?php

namespace mysli\curl; class __setup
{
    const __use = 'mysli.toolkit.config';

    static function enable()
    {
        $c = config::select('mysli.curl');
        $c->reset([
            'default' => [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING       => '',
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_MAXREDIRS      => 8
            ],
            'user_agent'      => true,
            'costume_agent'   => 'Mozilla/5.0 (X11; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0',
            'cookie_filename' => 'cookies.txt'
        ]);

        return $c->save();
    }

    static function disable()
    {
        return config::select('mysli.curl')->destroy();
    }
}
