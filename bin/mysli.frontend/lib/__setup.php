<?php

namespace mysli\frontend; class __setup
{
    const __use = '
        .{ theme }
        mysli.toolkit.{
            config,
            router,
            fs.fs -> fs,
            fs.dir -> dir,
            fs.file -> file
        }
    ';

    static function enable()
    {
        $c = config::select('mysli.frontend');
        $c->init([

            'locale.from'    => [ 'string', null ],
            'locale.default' => [ 'string', 'us' ],
            'locale.accept'  => [ 'array',  [ 'us' => 'en-us' ] ],

            'theme.active'   => [ 'string', 'default' ]
        ]);

        /*
        Return...
         */
        return

        // Create Directory With Default Tehemes
        dir::create(fs::cntpath('themes/default'))

        and

        // Write Default Theme
        file::write(
            fs::cntpath('themes/default/theme.ym'),
            'source: [ mysli.frontend, assets/theme ]'
        )

        and

        // Add Route Which Will Handle 404
        router::add(
            'mysli.frontend.frontend',
            'error404',
            router::route_special
        )

        and

        !! dir::copy(
            fs::pkgreal('mysli.frontend', 'assets/theme/public'),
            fs::pubpath('themes/default')
        )

        and

        // Save config
        $c->save()

        // Done
        ;
    }

    static function disable()
    {
        // Remove default theme
        dir::remove(fs::cntpath('themes/default'));

        // Unregister route
        router::remove('*@mysli.frontend.frontend');

        // Drop Config
        config::select('mysli.frontend')->destroy();

        // Done
        return true;
    }
}
