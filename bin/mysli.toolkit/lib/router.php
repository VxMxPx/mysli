<?php

namespace mysli\toolkit; class router
{
    const __use = '.{
        log,
        request,
        event,
        response,
        fs.fs -> fs,
        json,
        exception.router
    }';

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
    protected static $filters = [
        'any'      => '(.*?)',
        'slug'     => '([a-z0-9_\\-]+)',
        'alpha'    => '([a-z]+)',
        'numeric'  => '([0-9]+)',
        'alphanum' => '([a-z0-9]+)',
    ];

    /**
     * Full absolute path to the routes json file.
     * --
     * @var string
     */
    protected static $routes_file;

    /**
     * Containing all registered routes.
     * --
     * @var array
     */
    protected static $routes = [];

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

        if (static::$routes_file)
            throw new exception\router("Already initialized.", 10);

        if (!file::exists($path))
            throw new exception\router("File not found: `{$path}`", 20);

        static::$routes_file = $path;
        static::read();
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
     * continue to search and if another route will not be found, 404 will be
     * returned.
     *
     * The default type of route is `router::route_normal`, which will treat
     * added routes as of normal priority. In rare cases (like backend actions),
     * `router::route_high` can be used for high priority routes.
     * Another option is, `router::route_low`, to be checked at the end,
     * if nothing else matched.
     *
     * There are also `router::route_before` and `router::route_after` which
     * will run before and after every other route. These two will not stop
     * the router, event they return true, and will be always checked. They're
     * mean for special actions (like setting language, etc...).
     *
     * Finally there is `router::route_special` which cover special actions,
     * such as errors and index.
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
     *             // Do things...
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
     *        can be omitted when `$route` is an array.
     *
     * @param mixed $route
     *        Null   allowed when type is route_before or route_after.
     *        String when type is route_special, or if a single route is being added.
     *        Array  to add multiple routes. Format should be ['method' => 'route']
     *
     *        Route's format must be: `REQUEST_METHOD:[prefix/]segment/segment`.
     *
     *        `REQUEST_METHOD` can be: `POST`, `GET`, `PUT`, `DELETE` or `ANY`.
     *
     *        `[prefix/]` is variable part of URI, for example, for a BLOG package,
     *        it might be `[blog/]`; user can later change it.
     *
     *        Segments can be named: `{segment|...}`, and must have specified type
     *        (which will be matched by regular expression): `{segment|alpha}`,
     *        predefined types are: numeric, alpha, alphanum, slug, any.
     *        User can specify a costume type (regular expression), by putting
     *        it in brackets `()` for example: `{segment|([a-z]{2}\.[0-9]{4})`.
     *
     * @param string $type
     *        router::route_before  Run before other routes.
     *        router::route_after   Run after other routes.
     *        router::route_special Special, use `$route`, accepts: index, error_404
     *        router::route_high    High priority route. It will be checked first.
     *        router::route_normal  Normal priority route.
     *        router::route_low     Low priority route. It will be checked last.
     *
     * @param boolean $write
     *        Save changes to file to keep them permanently.
     * --
     * @throws mysli\toolkit\exception\router 10 Invalid Required `\$to` format.
     * @throws mysli\toolkit\exception\router 20 Route with such ID already exists.
     * --
     * @return boolean
     */
    static function add($to, $route, $type=self::route_normal, $write=true)
    {
        // If multiple routes, loop then return
        if (is_array($route))
        {
            foreach ($route as $method => $route_line)
            {
                $tof = (strpos($to, '::')) ? $to : "{$to}::{$method}";
                static::add($to, $route_line, $type, false);
            }

            return $write ? static::write() : true;
        }

        /*
        Extract TO
         */
        if (!strpos($to, '::'))
        {
            if ($type === static::route_special)
                $to = "{$to}::{$route}";
            else
                throw new exception\router(
                    "Required `\$to` format is: `vendor.package.class::method`, ".
                    "expections are `\$route` is array or `\$type` is `route_special`",
                    10
                );
        }

        // Make Route ID method@vednor.package.class
        $rid = static::create_rid($to);

        // Cannot be duplicated
        if (isset(static::$routes[$type][$rid]))
            throw new exception\router(
                "Route with such ID already exists: `{$rid}`.", 20
            );


        list($method, $prefix, $croute) = static::extract_route($route);

        if ($type !== static::route_special)
        {
            list($regex, $parameters) = static::extract_parameters($croute, $prefix);
        }
        else
        {
            $regex = null;
            $parameters = [];
        }

        /*
        Set route by type
         */
        static::$routes[$type][$rid] = [
            'to'         => $to,
            'rid'        => $rid,
            'method'     => $method,
            'prefix'     => $prefix,
            'route'      => $route,
            'regex'      => $regex,
            'type'       => $type,
            'parameters' => $parameters
        ];

        return $write ? static::write() : true;
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
     * @throws mysli\toolkit\exception\router 10 Id need to contain `@` symbol.
     * --
     * @return array [ array $route, array $route ]
     */
    static function get($id)
    {
        // Init return
        $return = [];

        // Get type and regex-to from id.
        list($type, $regexto) = static::resolve_id($id);

        /*
        Loop through types, and find route(s)
         */
        foreach ($type as $in)
        {
            // No such type, go on...
            if (!isset(static::$routes[$in]))
                continue;

            foreach (static::$routes[$in] as $rid => $route)
            {
                if (preg_match($regexto, $rid))
                    $return["{$in}:{$rid}"] = $route;
            }
        }

        return $return;
    }


    /**
     * Update or set specific option for a route(s).
     * The following values cannot be changed:
     * - rid, type, regex (use route), parameters (use route)
     * Allowed to change:
     * - string to    vendor.package.class::method
     * - string route
     * - array  method,
     * - string prefix,
     * - ... any other costume parameter
     *
     * Any costume option added though update, can be accessed when route is
     * passed to method, with: `$route->option('costume_key')`
     *
     * Please note, this will modify all matching routes. If you specify full Id
     * (type:method@vendor.package.class), there's no chance of multiple routes
     * being matched.
     * --
     * @param mixed  $id    (@see static::get()) or Route Array
     * @param string $key
     * @param mixed  $value
     * --
     * @throws mysli\toolkit\expcetion\router 10 Invalid ID type.
     * @throws mysli\toolkit\expcetion\router 20 Route with such ID already exits.
     * --
     * @return mixed
     *         integer Count of modified routes if ID is string
     *         boolean Was saved successfully, if ID is array.
     */
    static function update($id, $key, $value)
    {
        /*
        ID is String
         */
        if (is_string($id)):
            // Init return
            $return = 0;

            // Get type and regex-to from id.
            list($type, $regexto) = static::resolve_id($id);

            // Loop through types, and find route(s)
            foreach ($type as $in)
            {
                // No such type, go on...
                if (!isset(static::$routes[$in]))
                    continue;

                foreach (static::$routes[$in] as $rid => $route)
                {
                    if (preg_match($regexto, $rid))
                    {
                        if (static::update($route, $key, $value))
                            $return++;
                    }
                }
            }

            return $return;
        endif;


        /*
        ID is an Array
         */
        if (!is_array($id) || !isset($id['rid']))
        {
            throw new exception\router("Invalid ID type.", 10);
        }

        // Assign for easier reading
        $route = $id;

        // Check types which needs to be handled in special way

        if ($key === 'to')
        {
            // New Route Id
            $nrid = static::create_rid($value);

            // If already set, exception...
            if (isset(static::$routes[$route['type']][$nrid]))
            {
                throw new exception\router(
                    "Route with such ID already exists: `{$nrid}`.", 20
                );
            }

            unset(static::$routes[$route['type']][$route['rid']]);

            // Set it
            $route['to'] = $value;
            $route['rid'] = $nrid;
        }
        elseif ($key === 'route')
        {
            list($method, $prefix, $nroute) = static::extract_route($value);

            $route['route']  = $value;
            $route['method'] = $method;
            $route['prefix'] = $prefix;

            if ($route['type'] !== static::route_special)
            {
                list($regex, $parameters) = static::extract_parameters(
                    $nroute, $prefix
                );

                $route['regex']  = $regex;
                $route['parameters'] = $parameters;
            }
        }
        elseif ($key === 'method')
        {
            // Set raw method
            $route['method'] = $value;

            // Reconstruct
            list($_, $prefix, $broute) = static::extract_route($route['route']);

            $method = implode('|', $value);

            if ($prefix)
                $prefix = "[{$prefix}]";

            $route['route'] = "{$method}:{$prefix}{$broute}";
        }
        elseif ($key === 'prefix')
        {
            list($method, $prefix, $broute) = static::extract_route($route['route']);

            if ($value)
                $new_prefix = "[{$value}]";
            else
                $new_prefix = '';

            // Re-assemble
            $froute = implode('|', $method).":{$new_prefix}{$broute}";

            // Re-load
            list($regex, $parameters) =
                static::extract_parameters($broute, $value);

            $route['prefix'] = $value;
            $route['route']  = $froute;
            $route['regex']  = $regex;
            $route['parameters'] = $parameters;
        }
        else
        {
            $route[$key] = $value;
        }

        static::$routes[$route['type']][$route['rid']] = $route;

        return static::write();
    }

    /**
     * Remove specific route(s).
     * --
     * @param string $id (@see static::get())
     * --
     * @return integer Amount of removed items.
     */
    static function remove($id)
    {
        // Init return
        $return = 0;

        // Get type and regex-to from id.
        list($type, $regexto) = static::resolve_id($id);

        /*
        Loop through types, and find route(s)
         */
        foreach ($type as $in)
        {
            // No such type, go on...
            if (!isset(static::$routes[$in]))
                continue;

            foreach (static::$routes[$in] as $rid => $route)
            {
                if (preg_match($regexto, $rid))
                {
                    unset(static::$routes[$id][$rid]);
                    $return++;
                }
            }
        }

        if ($return > 0)
            static::write();

        return $return;
    }

    /**
     * Get number of routes by id.
     * --
     * @param string $id
     * --
     * @return integer
     */
    static function count($id)
    {
        return count(static::get($id));
    }

    /**
     * Dump who array of raw routes.
     * --
     * @param string $type Only routes of particular type; Null for all.
     * --
     * @throws mysli\toolkit\exception\router 10 Invalid type.
     * --
     * @return array
     */
    static function dump($type=null)
    {
        if (!$type)
            return static::$routes;
        elseif (array_key_exists($type, static::$routes))
            return static::$routes[$type];
        else
            throw new exception\router("Invalid type `{$type}`.", 10);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Extract route, return: method, prefix, new route without method and prefix.
     * --
     * @param string $route
     * --
     * @return array [ array $method, string $prefix, string $route ]
     */
    static protected function extract_route($route)
    {
        $method = null;

        if (preg_match('/^([a-z\|]+)\:(.*?)$/i', $route, $match))
        {
            $method = strtoupper($match[1]);
            $route  = $match[2];

            // Will be set to all bellow
            if ($method !== 'ANY')
                $method = explode('|', $method);
            else
                $method = null;
        }

        $method = $method ?: [ 'GET', 'POST', 'DELETE', 'PUT' ];

        // Extract prefix
        if (preg_match('/^\[([a-z0-9_\-\/]+)\](.*?)$/i', $route, $match))
        {
            $prefix = $match[1];
            $route  = $match[2];
        }
        else
        {
            $prefix = null;
        }

        return [ $method, $prefix, $route ];
    }

    /**
     * Resolve route, return regex format route and parameters.
     * This must be route without prefix and method.
     * --
     * @param string $route
     * @param string $prefix
     * --
     * @throws mysli\toolkit\exception\router 10 Invalid filter for route.
     * --
     * @return array
     *         [ string $regex, array $parameters ]
     */
    protected static function extract_parameters($route, $prefix)
    {
        // Before/after can register null route
        if (!$route)
            return [ null, [] ];

        /*
        If there's any route left, Extract parameters
         */
        $parameters = [];
        $regex      = null;


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
            if (preg_match('/^\{([a-z_]+)\|((?:[a-z]+)|(?:\(.*?\)))\}(.*?)$/i', $segment, $match))
            {
                list($_, $parameter, $filter, $extra) = $match;
                $parameters[] = $parameter;

                if (substr($filter, 0, 1) !== '(')
                {
                    if (isset(static::$filters[$filter]))
                        $filter = static::$filters[$filter];
                    else
                        throw new exception\router(
                            "Invalid filter: `{$filter}` for `{$route}`.", 10
                        );
                }

                if ($extra)
                    $extra = preg_quote($extra);

                $regex .= "/{$filter}{$extra}";
            }
            else
            {
                $regex .= '/'.preg_quote($segment);
            }
        }
        // Finish regex
        $regex   = ltrim($regex, '/');
        $regex   = "<^{$prefix}{$regex}$>i";

        return [ $regex, $parameters ];
    }

    /**
     * Get route Id from TO.
     * Example: vendor.package.class::method => method@vendor.package.class
     * --
     * @param  string $to
     * --
     * @return string
     */
    protected static function create_rid($to)
    {
        $rid = explode('::', $to, 2);
        return $rid[1].'@'.$rid[0];
    }

    /**
     * Resolve ID to get type and regex-to.
     * --
     * @param string $id
     * --
     * @return array [ array $type, string $regexto ]
     */
    protected static function resolve_id($id)
    {
        /*
        Extract type if exists
         */
        if (strpos($id, ':'))
            list($type, $id) = explode(':', $id, 2);
        else
            $type = null;

        /*
        Get TO
         */
        if (!strpos($id, '@'))
            throw new exception\router("Id need to contain `@` symbol.", 10);

        // Regex
        $regexto = $id;
        $regexto = preg_quote($regexto);
        $regexto = str_replace('\\*', '.*?', $regexto);
        $regexto = "/^{$regexto}$/";

        /*
        Define search type
         */
        if ($type)
            $type = [ $type ];
        else
            $type = [ 'before', 'after', 'special', 'high', 'normal', 'low' ];

        return [ $type, $regexto ];
    }

    /*
    Read / Write
     */

    protected static function read()
    {
        static::$routes = json::decode_file(static::$routes_file, true);
    }

    protected static function write()
    {
        return json::encode_file(static::$routes_file, static::$routes);
    }
}
