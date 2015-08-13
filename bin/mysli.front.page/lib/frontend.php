<?php

namespace mysli\front\page; class frontend
{
    static function index($route)
    {
        $route->set_option('page', 'index');
        return static::page($route);
    }

    static function page($route)
    {
        $language = $route->option('i18n.language', 'en-us');
        $page = page::get_by_id($route->option('page'), $language);

        if (!$page)
        {
            return false;
        }

        $route->set_option(
            [
                'frontend.variables' =>
                [
                    'title' => $page->title(),
                    'page'  => $page
                ],
                'frontend.template' =>
                [
                    '.page',
                    'mysli.front.page:page.tpl.html'
                ]
            ],
            null
        );

        return true;
    }
}
