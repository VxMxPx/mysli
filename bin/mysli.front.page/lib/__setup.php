<?php

namespace mysli\front\page; class __setup
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
        dir::create(fs::cntpath('pages'))

        and

        dir::copy(
            fs::pkgreal('mysli.front.page/assets/pages'),
            fs::cntpath('pages')
        )

        and

        // Create router to handle pages
        router::add('mysli.front.page.frontend', [
            'page' => 'GET:{page|(a-z0-9_\-\/)}.html'
        ], router::route_low)

        and

        // Create router to handle index
        router::add('mysli.front.page.frontend', 'index', router::route_special)

        // Done
        ;
    }

    static function disable()
    {
        return

        // Remove default route
        !!router::remove('*@mysli.front.page.frontend')

        // Done
        ;
    }

    static function clean()
    {
        return dir::remove(fs::cntpath('pages'));
    }
}
