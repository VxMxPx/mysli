<?php

namespace mysli\blog; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ route }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        $categories = <<<cat
default:
    name: Default Categoy
    description: Default category.
    language: [ null ]
cat;

        if (!file::exists(fs::cntpath('blog', 'categories.ym')))
        {
            // Default directories and
            dir::create(fs::cntpath('blog'));
            file::write(fs::cntpath('blog', 'categories.ym'), $categories);
        }

        return

        // Add Routes
        route::add(
            'mysli.blog.frontend::archive',
            'ANY',
            '/r',
            'medium')

        and

        route::add(
            'mysli.blog.frontend::ppost',
            'ANY',
            '/r/<year:digit>/<post:slug>/<page:slug>.html',
            'medium')

        and

        route::add(
            'mysli.blog.frontend::post',
            'ANY',
            '/r/<year:digit>/<post:slug>.html',
            'medium')

        and

        route::add(
            'mysli.blog.frontend::tag',
            'ANY',
            '/r/tag/<tag:slug>',
            'medium')

        and

        route::write()

        // Done
        ;
    }

    static function disable()
    {
        return !!route::remove('mysli.blog.frontend::*');
    }
}
