<?php

namespace mysli\blog; class frontend
{
    const __use = <<<fin
    .{ blog }
    mysli.i18n
    mysli.toolkit.{ config }
    mysli.frontend.{ frontend -> fe }
fin;

    static function archive()
    {
        config::select('mysli.blog', 'cache.reload-on-access') and blog::refresh_list();

        $list = blog::list_all();
        krsort($list);

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'front' => [
                'title' => i18n::select(['mysli.blog', 'en', null], 'ARCHIVE')
            ],
            'posts' => $list
        ]);

        return true;
    }

    static function tag($id)
    {
        config::select('mysli.blog', 'cache.reload-on-access') and blog::refresh_list();

        $list = blog::list_by_tag($id);
        krsort($list);

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'front' => [ 'title' => ucfirst($id) ],
            'posts' => $list
        ]);

        return true;
    }

    static function ppost($year, $id, $page)
    {
        return static::post($year, $id, $page);
    }

    static function post($year, $id, $page='_default')
    {
        if (!blog::has($year, $id))
        {
            return false;
        }

        $post = blog::get_one($year, $id);

        if (!$post)
        {
            return false;
        }

        $c = config::select('mysli.blog');

        if ($c->get('cache.reload-on-access') && !$post->is_cache_fresh())
        {
            $post->make_cache();

            if ($c->get('version.up-on-reload'))
            {
                $post->up_version();
            }

            if ($c->get('media.republish-on-reload'))
            {
                $post->unpublish_media();
                $post->publish_media();
            }
        }

        if (!$post->get('published', true))
        {
            return false;
        }

        fe::render(['blog-post', ['mysli.blog', 'post']], [
            'front' => [ 'title' => $post->get('title') ],
            'post'  => $post->as_array($page)
        ]);

        return true;
    }
}
