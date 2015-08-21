<?php

namespace mysli\assets; class __setup
{
    const __use = '
        mysli.toolkit.{ config }
    ';

    static function enable()
    {
        $c = config::select('mysli.assets');
        $c->init([
            'debug' => [ 'boolean', false ]
        ]);

        return $c->save();
    }

    static function cleanup()
    {
        return config::select('mysli.assets')->destroy();
    }
}
