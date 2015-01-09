<?php

namespace mysli\web\assets;

// Define includes
function __init() {

    // Base
    $base = '
        mysli/framework/exception/{...} AS framework/exception/{...}
        mysli/framework/fs/{fs,file,dir}
        mysli/framework/type/{arr,str}
        mysli/framework/pkgm
        mysli/framework/ym
        mysli/util/config
        mysli/web/web
    ';

    // Script
    $script = '
        mysli/framework/cli/{output,input,param,util} AS {cout,cinput,cparam,cutil}
        mysli/web/assets AS root/assets
    ';

    // Tplp
    $tplp = '
        mysli/web/assets
    ';

    __use('mysli\\web\\assets',         $base);
    __use('mysli\\web\\assets\\script', $base.$script);
    __use('mysli\\web\\assets\\tplp',   $base.$tplp);
}
