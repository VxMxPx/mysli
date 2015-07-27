<?php

namespace mysli\toolkit; class router
{
    const __use = '.{log, request, event, response, fs, json, exception.router}';

    /*
    Route types
     */
    const route_before  = 'before';
    const route_after   = 'after';
    const route_special = 'special';
    const route_high    = 'high';
    const route_normal  = 'normal';
    const route_low     = 'low';

    /**
     * Used when a new route is registered. Resolve one-word filters
     * --
     * @var array
     */
    private static $filters = [
        'any'      => '(.*?)',
        'slug'     => '([a-z0-9_-]+)',
        'alpha'    => '([a-z]+)',
        'numeric'  => '([0-9]+)',
        'alphanum' => '([a-z0-9]+)',
    ];

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
     * Register a new route.
     *
     * Register route will be added to the list of routes in regular expression
     * format, and check on each request. If matched, the provided method will
     * be called, with one argument: `route` (@see mysli\toolkit\router\route).
     * The method needs to return boolean. If False is returned, router will
     * continue to search and if another route will be not found, 404 will be
     * returned.
     *
     * The default type of route is `router::route_normal`, which will treat
     * added routes as of normal priority. In rare cases (like backend actions),
     * `router::route_high` can be used for high priority routes.
     * In other case, `router::route_low`, to be checked at the end, if nothing
     * else matched.
     * --
     * @example
     *
     *     // Register blog post...
     *     router::add(
     *         'vendor.blog.controller',
     *         ['post' => 'GET:[blog/]post/{year|numeric}/{id|slug}.html']
     *     );
     *
     *     // in vendor.blog/lib/controller.php
     *     namespace vendor\blog; class controller
     *     {
     *         static function post(mysli\toolkit\router\route $route)
     *         {
     *             list($id, $year) = $route->parameter(['id', 'year']);
     *             // Do the things...
     *             return true;
     *         }
     *     }
     *
     *     // Example of a simple i18n handler...
     *     router::add(
     *         'vendor.i18n.controller::set',
     *         'ANY:{language|([a-z]{2})}/...',
     *         router::route_before
     *     );
     *
     *     // in vendor.i18n/lib/controller.php
     *     namespace vendor\i18n; class controller
     *     {
     *         static function set(mysli\toolkit\router\route $route)
     *         {
     *             // Grab language
     *             $language = $route->parameter('language');
     *
     *             // Modify URL
     *             $route->set_uri(substr($route->url(), 3));
     *
     *             // Set language to be down the line...
     *             $route->set_option('vendor.i18n.language', $language);
     *
     *             return true;
     *         }
     *     }
     * --
     * @param string $to
     *        The route handler. Format: vendor.package.class::method, method
     *        can be omitted when routes array is provided.
     *
     * @param mixed $route
     *        Null   when type is route_before or route_after.
     *        String when type is route_special, or if a single route is being added.
     *        Array  to add multiple routes. Format should be ['method' => 'route']
     *
     *        Route's format must be: `REQUEST_METHOD:[prefix/]segment/segment`.
     *
     *        `REQUEST_METHOD` can be: `POST`, `GET`, `PUT`, `DELETE` or `ANY`.
     *
     *        `[prefix/]` is variable part of URI, for example, for a BLOG package,
     *        it might be `[blog/]`, but user can later change.
     *
     *        Segments can be named: `{segment|...}`, and must have specified type
     *        (which will be matched by regular expression): `{segment|alpha}`,
     *        predefined types are: numeric, alpha, alphanum, slug, any.
     *        User can specify a costume type (regular expression), by putting
     *        it in bracket `()` for example: `{segment|([a-z]{2}\.[0-9]{4})`.
     *
     * @param string $type
     *        router::route_before  Run before each route.
     *        router::route_after   Run after each route.
     *        router::route_special Special, use `$route`, accepts: index, error_404
     *        router::route_high    High priority route. It will be checked first.
     *        router::route_normal  Normal priority route.
     *        router::route_low     Low priority route. It will be checked last.
     *
     * @param boolean $write
     *        Save changes to file.
     * --
     * @throws mysli\toolkit\exception\router 10 Invalid type.
     * @throws mysli\toolkit\exception\router 20 Invalid filter for route.
     * --
     * @return boolean
     */
    function add($to, $route, $type=self::route_normal, $write=true)
    {
        // If multiple routes, loop then return
        if (is_array($route))
        {
            foreach ($route as $method => $route_line)
            {
                $tof = (strpos($to, '::')) ? $to : "{$to}::{$method}";
                self::add($to, $route_line, $type, false);
            }

            return $write ? self::write() : true;
        }

        /*
        Extract call
         */
        $call = explode('::', $to);

        /*
        Special route, done right here.
         */
        if ($type === self::route_special)
        {
            self::$routes[self::route_special][$route] = [
                'call'       => $call,
                'method'     => $method,
                'type'       => self::route_special,
                'route'      => $route,
                'prefix'     => null,
                'regex'      => null,
                'parameters' => []
            ];

            return $write ? self::write() : true;
        }

        /*
        Extract method
         */
        if (preg_match('/^([a-z\|]+)\:.*$/i', $route, $match))
        {
            $method = $match[1];
            $rotue  = $match[2];
            unset($match);

            // Will be set to all bellow
            if (strtolower($method) === 'any')
                $method = null;
            else
                $method = explode('|', $method);
        }

        if (!$method)
            $method = [ 'get', 'post', 'delete', 'put' ];

        /*
        Extract prefix
         */
        if (preg_match('/^\[([a-z\/]+)\](.*)$/i', $route, $match))
        {
            $prefix = $match[1];
            $route  = $match[2];
        }
        else
        {
            $prefix = null;
        }

        /*
        If there's any route left, Extract parameters
         */
        $parameters = [];
        $regex      = null;

        if ($route)
        {
            $segments = explode('/', $route);

            foreach ($segments as $id => $segment)
            {
                // The end
                if ($segment === '...')
                {
                    $regex .= '/?.*?';
                    break;
                }

                // Special segment?
                if (preg_match('/^\{([a-z_]+)\|(?:([a-z]+)|(\(.*?\)))\}$/i', $segment, $match))
                {
                    list($_, $parameter, $filter) = $match;
                    $parameters[] = $parameter;

                    if (substr($filter, 0, 1) !== '(')
                    {
                        if (isset(self::$filters[$filter]))
                            $filter = self::$filters[$filter];
                        else
                            throw new exception\router(
                                "Invalid filter: `{$filter}` for `{$route}`.", 20
                            );
                    }

                    $regex .= "/{$filter}";
                }
                else
                {
                    $regex .= '/'.preg_quote($segment);
                }
            }
            // Finish regex
            $regex   = ltrim($regex, '/');
            $regex   = "<^{$prefix}{$regex}$>i";
        }

        /*
        Set route by type
         */
        if (isset(self::$routes[$type]))
        {
            self::$routes[$type][] = [
                'call'       => $call,
                'method'     => $method,
                'prefix'     => $prefix,
                'route'      => $route,
                'regex'      => $regex,
                'type'       => $type,
                'parameters' => $parameters
            ];
        }
        else
            throw new exception\router("Invalid type: `{$type}`.", 10);

        return $write ? self::write() : true;
    }

    /**
     * Get a particular route(s) by id.
     * --
     * @param string $id
     *        For example, from most to less specific:
     *        - type:method@vendor.package.class
     *            - special:index@vendor.blog.controller
     *            - before:set@vendor.i18n.controller
     *            - normal:*@vendor.blog.controller
     *        - method@vendor.package.class
     *            - post@vendor.blog.controller
     *        - *@vendor.package.class
     *            - *@vendor.blog.controller
     *        - *@vendor.package.*
     *            - *@vendor.blog.*
     *
     * @param string $type
     *        Null for any types.
     * --
     * @return array [ array $route, array $route ]
     */
    function get($id)
    {

    }

    /**
     * Update or set specific option for a route(s).
     * The following values cannot be changed:
     * - call, type, route, regex, parameters
     * Allow to change:
     * - array method, string prefix, ... any other costume parameter
     *
     * Any costume option added though update, can be accessed when route is
     * passed to method, with: `$route->option('costume_key')`
     * --
     * @param string $id    (@see self::get())
     * @param string $key
     * @param mixed  $value
     * --
     * @return boolean
     */
    function update($id, $key, $value)
    {

    }

    /**
     * Remove specific route(s).
     * --
     * @param string $id (@see self::get())
     * --
     * @return boolean
     */
    function remove($id)
    {

    }

    /**
     * Get number of routes by id.
     * --
     * @param string $id
     * --
     * @return integer
     */
    function count($id)
    {

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
