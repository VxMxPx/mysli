<?php

namespace mysli\blog; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ route }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        $settings = <<<fin
default:
    title: null
    author: Anonymous
    date: 1984-04-16
    tags: []
    published: Yes
feed:
    title: null
    limit: 20
fin;
        $categories = <<<fin
default:
    name: Default Category
    description: This is a description of a default category.
    language: [ null ]
fin;

        if (!dir::create(fs::cntpath('blog'))) return false;

        if (!file::exists(fs::cntpath('blog', 'categories.ym')))
        {
            if (!file::write(fs::cntpath('blog', 'categories.ym'), $categories))
                return false;
        }

        if (!file::exists(fs::cntpath('blog', 'settings.ym')))
        {
            if (!file::write(fs::cntpath('blog', 'settings.ym'), $settings))
                return false;
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

        route::add(
            'mysli.blog.frontend::feed',
            'GET',
            '/r/feed',
            'medium')

        and

        route::write()

        // Done
        ;
    }

    static function disable()
    {
        return route::remove('mysli.blog.frontend::*')
        and route::write();
    }
}
