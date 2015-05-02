<?php

namespace mysli\util\curl\setup;

__use(__namespace__, '
    mysli.util.config
    mysli.framework.fs/fs,dir
');

function enable()
{
    $c = config::select('mysli.util.curl');
    $c->reset([
        // Weather to acquire an agent from user...
        'user_agent' => true,
        // If user's agent's not set, or set to false, what to use as a fallback
        'costume_agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0',
        // Cookie will be stored in {datpath}/{cookie_filename}
        'cookie_filename' => 'default.txt',
        // CURL overwrites, be very careful with those!
        // Only applied when calling ::post, ::get
        'default' => [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_MAXREDIRS      => 8
        ]
    ]);

    return dir::create(fs::datpath('mysli/util/curl')) &&
        $c->save();
}

function disable()
{
    $c = config::select('mysli.util.curl');
    return dir::remove(fs::datpath('mysli/util/curl')) &&
        $c->destroy();
}
