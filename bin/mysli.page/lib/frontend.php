<?php

namespace mysli\page; class frontend
{
    const __use = <<<fin
    .{ page }
    mysli.toolkit.{ config }
    mysli.frontend.{ frontend -> fe }
fin;

    static function index()
    {
        return static::page('index');
    }

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

        if ($c->get('cache.reload-on-access') && !$page->is_cache_fresh())
        {
            $page->refresh_cache();

            if ($c->get('version.up-on-reload'))
            {
                $page->new_version();
            }

            if ($c->get('media.republish-on-reload'))
            {
                $page->unpublish_media();
                $page->publish_media();
            }
        }

        if (!$page->get('published', true))
        {
            return false;
        }

        fe::render(['page', ['mysli.page', 'page']], [
            'page' => $page->as_array()
        ]);

        return true;
    }
}
