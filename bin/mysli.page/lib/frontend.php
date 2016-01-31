<?php

namespace mysli\page; class frontend
{
    const __use = <<<fin
    .{ pages }
    mysli.frontend.{ frontend -> fe }
fin;

    static function index()
    {
        return static::page('index');
    }

    static function page($path)
    {
        if (!pages::has($path))
        {
            return false;
        }

        $page = pages::by_path($path);

        if (!$page)
        {
            return false;
        }

        if (!$page->is_latest_cache())
        {
            $page->refresh_cache();
            $page->write_version();
        }

        if (!$page->get('published'))
        {
            return false;
        }

        fe::render(['page', ['mysli.page', 'page']], [
            'page' => $page->as_array()
        ]);

        return true;
    }
}
