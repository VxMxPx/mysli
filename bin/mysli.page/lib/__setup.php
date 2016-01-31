<?php

namespace mysli\front\page; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ router }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        return

        // Create defaule theme directory
        dir::create(fs::cntpath('pages'))

        and

        dir::copy(fs::pkgreal('mysli.page/assets/pages'), fs::cntpath('pages'))

        and

        // Create router to handle pages
        route::add('mysli.page.frontend::index', 'ANY', '*index', 'low') and
        route::add('mysli.page.frontend::page', 'ANY', '/<page|page>.html', 'low') and
        route::write()

        // Done
        ;
    }

    static function disable()
    {
        return

        // Remove default route
        !!router::remove('mysli.page.frontend::*')

        // Done
        ;
    }
}
