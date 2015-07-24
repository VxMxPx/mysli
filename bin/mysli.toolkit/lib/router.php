<?php

namespace mysli\toolkit; class router
{
    const __use = '.{log, request, event, response, fs, json, exception.router}';

    /**
     * Full absolute path to the routes json file.
     * --
     * @var string
     */
    private static $routes_file;

    /**
     * Containing all registered routes.
     * --
     * @var array
     */
    private static $routes = [];

    /**
     * Load the routes repository.
     * --
     * @param string $path
     *        Specify a specific routes path, if not, default will be used.
     * --
     * @throws mysli\toolkit\router 10 Already initialized.
     * @throws mysli\toolkit\router 20 File not found.
     * --
     * @return boolean
     */
    static function __init($path=null)
    {
        $path = $path ?: fs::cfgpath('toolkit.routes.json');

        if (self::$routes_file)
            throw new exception\router("Already initialized.", 10);

        if (!file::exists($path))
            throw new exception\router("File not found: `{$path}`", 20);

        self::$routes_file = $path;
        self::read();
    }

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

    /*
    --- Individual routes ------------------------------------------------------
     */

    /**
     * Register a new route. You're probably want to use one of the shortcuts
     * to do this: (@see self::before), (@see self::after), (@see self::special),
     * (@see self::high), (@see self::normal), (@see self::low)
     * --
     * @param string $to
     *        Route handler: vendor.package.class::method (...)
     *
     * @param string $type
     *        Route type: @before, @after, @special, @high, @normal, @low
     *
     * @param array $options
     *        Additional options for route to be matched.
     * --
     * @return boolean
     */
    static function register($to, $type, array $options=[])
    {

    }

    /**
     * Shortcut, register an action before any route action is executed.
     * --
     * @param  string $to      Action handler: vendor.package.class::method
     * @param  array  $options Additional options.
     * --
     * @return boolean
     */
    static function before($to, array $options=[])
    {
        return self::register(array_merge([
            'to'   => $to,
            'type' => '@before'
        ], $options));
    }

    /**
     * Shortcut, register an action after any route action is executed.
     * --
     * @param  string $to      Action handler: vendor.package.class::method
     * @param  array  $options Additional options.
     * --
     * @return boolean
     */
    static function after($to, array $options=[])
    {
        return self::register(array_merge([
            'to'   => $to,
            'type' => '@after'
        ], $options));
    }

    /**
     * Handle a special event or route, like index page or an error.
     * --
     * @param  string $action
     *         Available actions are:
     *         - index When there's no route.
     *         - 404   Error, not found.
     *
     * @param string $to
     *         Action handler: vendor.package.class::method
     *
     * @param  array  $options Additional options.
     * --
     * @return boolean
     */
    static function special($action, $to, array $options=[])
    {
        return self::register(array_merge([
            'action' => $action,
            'to'     => $to,
            'type'   => '@special'
        ], $options));
    }

    /**
     * High priority route. This route will be matched first.
     * Useful for an back-end actions.
     * --
     * @param string $id
     *        And unique ID for this particular hander ($to).
     *
     * @param string $route
     *        Route to be matched.
     *
     * @param string $to
     *        Route handler: vendor.package.class::method(type $param='default')
     *
     * @param array  $options
     *        Additional options.
     * --
     * @return boolean
     */
    static function high($id, $route, $to, array $options=[])
    {
        return self::register(array_merge([
            'id'    => $id,
            'route' => $route,
            'to'    => $to,
            'type'  => '@high'
        ], $options));
    }

    /**
     * Normal priority route. This route will be matched after high priority
     * route, but before low priority.
     * Use for all regular actions, especially those which are prefixed,
     * like blog, etc...
     * --
     * @param string $id
     *        And unique ID for this particular hander ($to).
     *
     * @param string $route
     *        Route to be matched.
     *
     * @param string $to
     *        Route handler: vendor.package.class::method(type $param='default')
     *
     * @param array  $options
     *        Additional options.
     * --
     * @return boolean
     */
    static function normal($id, $route, $to, array $options=[])
    {
        return self::register(array_merge([
            'id'    => $id,
            'route' => $route,
            'to'    => $to,
            'type'  => '@normal'
        ], $options));
    }

    /**
     * Low priority route. This route will be matched last.
     * Useful for non-prefixed contents, like regular pages.
     * --
     * @param string $id
     *        And unique ID for this particular hander ($to).
     *
     * @param string $route
     *        Route to be matched.
     *
     * @param string $to
     *        Route handler: vendor.package.class::method(type $param='default')
     *
     * @param array  $options
     *        Additional options.
     * --
     * @return boolean
     */
    static function low($id, $route, $to, array $options=[])
    {
        return self::register(array_merge([
            'id'    => $id,
            'route' => $route,
            'to'    => $to,
            'type'  => '@low'
        ], $options));
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    private static function read()
    {
        self::$routes = json::decode_file(self::$routes_file, true);
    }

    private static function write()
    {
        return json::encode_file(self::$routes_file, self::$routes);
    }
}
