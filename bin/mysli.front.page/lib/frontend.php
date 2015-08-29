<?php

namespace mysli\front\page; class frontend
{
    static function index($route)
    {
        $route->set_segment('page', 'index');
        return static::page($route);
    }

    static function page($route)
    {
        $language = $route->option('i18n.language', 'en-us');
        // $page = page::get_by_id($route->segment('page'), $language);
        $page = [
            'title'      => 'Test Page',
            'created_on' => '2015-08-20T22:40:50+02:00',
            'body'       =>
                'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed '.
                'do eiusmod tempor incididunt ut labore et dolore magna aliqua. '.
                'Ut enim ad minim veniam, quis nostrud exercitation ullamco '.
                'laboris nisi ut aliquip ex ea commodo consequat. Duis aute '.
                'irure dolor in reprehenderit in voluptate velit esse cillum '.
                'dolore eu fugiat nulla pariatur. Excepteur sint occaecat '.
                'cupidatat non proident, sunt in culpa qui officia deserunt '.
                'mollit anim id est laborum.'
        ];

        if (!$page)
        {
            return false;
        }

        $route->set_option(
            [
                'frontend.variables' =>
                [
                    'title' => $page['title'],
                    'page'  => $page
                ],
                'frontend.template' =>
                [
                    'page',
                    'mysli.front.page:page.tpl.html'
                ]
            ],
            null
        );

        return true;
    }
}
