<?php

namespace mysli\blog; class frontend
{
    const __use = <<<fin
    .{ blog }
    mysli.toolkit.{ config }
    mysli.frontend.{ frontend -> fe }
fin;

    static function archive()
    {
        static::reload_cache();
        $list = blog::list_all();

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'posts' => $list
        ]);
    }

    static function tag($id)
    {
        static::reload_cache();
        $list = blog::list_by_tag($id);

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'posts' => $list
        ]);
    }

    static function post($year, $id)
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

        if ($c->get('cache.reload-on-access') && !$post->is_latest_cache())
        {
            $post->refresh_cache();

            if ($c->get('version.up-on-reload'))
            {
                $post->new_version();
            }

            if ($c->get('media.republish-on-reload'))
            {
                $post->unpublish_media();
                $post->publish_media();
            }
        }

        if (!$post->get('published'))
        {
            return false;
        }

        fe::render(['blog-post', ['mysli.blog', 'post']], [
            'post' => $post->as_array()
        ]);

        return true;
    }

    protected function reload_cache()
    {
        $c = config::select('mysli.blog');

        if ($c->get('cache.reload-on-access'))
        {
            blog::refresh_list();
        }
    }
}
