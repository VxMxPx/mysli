<?php

namespace mysli\cm\page; class __setup
{
    const __use = '
        mysli.toolkit.{
            router,
            fs.fs -> fs,
            fs.dir -> dir,
            fs.file -> file
        }
    ';

    static function enable()
    {
        return

        // Create defaule theme directory
        dir::create(fs::cntpath('themes/pages'))

        and

        // Create router to handle 404
        router::add('mysli.cm.page.frontend', [
            'page' => 'GET:{page|slug}.html'
        ], router::route_low)

        // Done
        ;
    }

    static function disable()
    {
        return

        // Remove default route
        !!router::remove('*@mysli.cm.page.frontend')

        // Done
        ;
    }
}
