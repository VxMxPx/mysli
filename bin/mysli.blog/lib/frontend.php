<?php

namespace mysli\blog; class frontend
{
    const __use = <<<fin
    .{ blog }
    mysli.i18n
    mysli.toolkit.{ config, request }
    mysli.frontend.{ frontend -> fe }
fin;

    /**
     * Render blog archive.
     * --
     * @return boolean
     */
    static function archive()
    {
        config::select('mysli.blog', 'cache.reload-on-access') and blog::refresh_list();

        $list = blog::list_all();
        krsort($list);

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'front' => [
                'subtitle' => i18n::select(['mysli.blog', 'en', null], 'ARCHIVE'),
                'type'     => 'blog-archive'
            ],
            'blog' => [
                'categories' => config::select('mysli.blog', 'tags.to-categories'),
                'is_archive' => true,
                'is_tag'     => false,
                'tag'        => null,
            ],
            'posts' => $list
        ]);

        return true;
    }

    /**
     * Render archive for a particular tag.
     * --
     * @param string $id
     * --
     * @return boolean
     */
    static function tag($id)
    {
        config::select('mysli.blog', 'cache.reload-on-access') and blog::refresh_list();

        $list = blog::list_by_tag($id);
        krsort($list);

        $categories = config::select('mysli.blog', 'tags.to-categories');

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'front' => [
                'subtitle' => ucfirst($id),
                'type'     => 'blog-tag',
                'quid'     => 'blog-tag-'.$id,
            ],
            'blog' => [
                'categories' => $categories,
                'is_archive' => false,
                'is_tag'     => true,
                'tag'        => $id
            ],
            'posts' => $list
        ]);

        return true;
    }

    /**
     * Render a particular page of a post.
     * --
     * @param integer $year
     * @param string  $id
     * @param string  $page
     * --
     * @return boolean
     */
    static function ppost($year, $id, $page)
    {
        return static::post($year, $id, $page);
    }

    /**
     * Render a particular post.
     * --
     * @param integer $year
     * @param string  $id
     * @param string  $page
     * --
     * @return boolean
     */
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

        // Do cache
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

        // Check publish status
        if (!$post->get('published', true))
        {
            // Final chance to view in dev-access
            if (!$post->get('dev-access')
                || request::get('access') !== $post->get('dev-access'))
            {
                return false;
            }
        }

        fe::render(['blog-post', ['mysli.blog', 'post']], [
            'front' => [
                'subtitle' => $post->get('title'),
                'type'     => 'blog-post',
                'quid'     => 'post-'.str_replace(['/', '.'], '-', $post->get_quid())
            ],
            'blog' => [
                'categories' => config::select('mysli.blog', 'tags.to-categories'),
                'is_archive' => false,
                'is_tag'     => false,
                'tag'        => null,
            ],
            'page'    => $post->as_array($page),
            'article' => $post->as_array(null),
        ]);

        return true;
    }
}
