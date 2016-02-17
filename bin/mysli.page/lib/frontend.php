<?php

namespace mysli\page; class frontend
{
    const __use = <<<fin
    .{ page }
    mysli.toolkit.{ config }
    mysli.frontend.{ frontend -> fe }
fin;

    /**
     * Render index page.
     * --
     * @return boolean
     */
    static function index()
    {
        return static::page('index');
    }

    /**
     * Render any page.
     * --
     * @param string $path
     * --
     * @return boolean
     */
    static function page($path)
    {
        if (!page::has($path))
        {
            return false;
        }

        $page = page::by_path($path);

        if (!$page)
        {
            return false;
        }

        $c = config::select('mysli.page');

        // Cache handling
        if ($c->get('cache.reload-on-access') && !$page->is_cache_fresh())
        {
            $page->make_cache();

            if ($c->get('version.up-on-reload'))
            {
                $page->up_version();
            }

            if ($c->get('media.republish-on-reload'))
            {
                $page->unpublish_media();
                $page->publish_media();
            }
        }

        // Must be published
        if (!$page->get('published', true))
        {
            return false;
        }

        // Render finally
        fe::render(['page', ['mysli.page', 'page']], [
            'front' => [
                'subtitle' => $page->get('title'),
                'type'     => 'page',
                'quid'     => 'page-'.str_replace(['/', '.'], '-', $page->get_quid())
            ],
            'article' => $page->as_array(),
            'post'    => $page->as_array(null),
        ]);

        return true;
    }
}
