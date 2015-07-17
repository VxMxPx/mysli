<?php

namespace mysli\toolkit; class router
{

    const __use = '.{log, request, event, response}';

    /**
     * Resolve routes and trigger an event.
     * --
     * @event toolkit.router::resolve.route(string $method, string $route)
     * @event toolkit.router::resolve.404(string $method, string $route)
     */
    static function resolve()
    {
        // Get route and remove any * < > character.
        $route = implode('/', request::segment());
        $route = str_replace(['*', '<', '>'], '', $route);
        $method = strtolower(request::method());

        event::trigger("toolkit.router::resolve.route", [$method, $route]);

        if (response::get_status() === 0)
        {
            log::info(
                "No answer for: `{$method}:{$route}`, going 404.", __CLASS__
            );

            response::set_status(404);
            event::trigger('toolkit.router::resolve.404', [$method, $route]);
        }
    }
}
