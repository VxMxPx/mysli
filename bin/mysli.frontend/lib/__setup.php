<?php

namespace mysli\frontend; class __setup
{
    const __use = 'mysli.toolkit.{
        config,
        router,
        fs.fs -> fs,
        fs.dir -> dir,
        fs.file -> file
    }';

    static function enable()
    {
        $c = config::select('mysli.frontend');
        $c->init([

            'locale.from'    => [ 'string', null ],
            'locale.default' => [ 'string', 'en' ],
            'locale.accept'  => [ 'array',  ['en-us' => 'us'] ],

            'template.active'  => [ 'string', 'default' ]
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
            'source: [ mysli.toolkit, assets/theme ]'
        )

        and

        // Add Route Which Will Handle 404
        router::add(
            'mysli.frontend.frontend',
            'error404',
            router::route_special
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
