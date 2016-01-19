<?php

namespace mysli\toolkit; class route
{
    const __use = '
        .{ clist, exception.route }
    ';

    /**
     * Routes stored as an array.
     * --
     * @var array
     */
    protected static $r = [];

    /**
     * Route registry file.
     * --
     * @var string
     */
    protected static $r_file;

    /**
     * Route segment types.
     * --
     * @var array
     */
    protected static $s_type = [
        'digit' => '[0-9]+',
        'alpha' => '[a-z]+',
        'alnum' => '[a-z0-9]+',
        'slug'  => '[a-z0-9_\+\-]',
        'path'  => '[a-z0-9_+-\/]+',
    ];

    /**
     * Options for clist.
     * --
     * @var array
     */
    protected static $r_options = [
        'map'         => [ 'call', 'method', 'route' ],
        'category_to' => '{ID}',
        'categories'  => [ 'high', 'medium', 'low' ]
    ];

    /**
     * Initialize route.
     * --
     * @param string $path
     * --
     * @return boolean
     */
    static function __init($path=null)
    {
        $path = $path ?: fs::cfgpath('routes.list');

        if (static::$r_file)
            throw new exception\router("Already initialized.", 10);

        if (!file::exists($path))
            throw new exception\router("File not found: `{$path}`", 20);

        static::$r_file = $path;

        return static::reload();
    }

    /**
     * Dump currently set routes.
     * --
     * @return array
     */
    static function dump()
    {
        return static::$r;
    }

    /**
     * Execute particular route. If matched, call the specified method.
     * --
     * @param string $url
     * --
     * @return boolean
     */
    static function execute($url)
    {
        foreach (static::$r as $priority => $routes)
        {
            foreach ($routes as $rid => $route)
            {
                // Special case
                if ($route['resolved'] === '*')
                {
                    continue;
                }

                // Resolve request
                if (!is_array($route['request']))
                {
                    if ($route['request'] === 'ANY')
                    {
                        $route['request'] = [ 'GET', 'POST', 'PUT', 'DELETE' ];
                    }
                    else
                    {
                        $route['request'] = explode(',', $route['request']);
                    }
                }

                // Not proper request method
                if (!in_array(request::method(), $route['method']))
                {
                    continue;
                }

                if (preg_match($route['resolved'], $url, $m))
                {
                    unset($m[0]);
                    $call = str_replace('.', '\\', $route['call']);

                    if (call_user_func_array($call, $m))
                    {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * Add a new route to the list.
     * --
     * @param string $call
     *        On route match, call: `vendor.package.class::method`.
     *
     * @param string $method
     *        Request method: GET,POST,PUT,DELETE,ANY
     *        Use commas to specify multiple: GET,DELETE
     *
     * @param string $route
     *        Route to be matched, e.g.:
     *        - /users/post.html  is route with no parameters.
     *        - /<year:digit>/<post:slug>.html  `year` and `post` are parameters
     *          which will be passed to the $call. They're `digit` and `slug`,
     *          see `Types` for all available types.
     *        - /<post:slug>.html/<page?:digit> the last parameter is optional
     *          hence `?`. This will apply to whole segment, e.g.:
     *          `<post?:slug>.html` would make whole segment
     *          including `.html` optional.
     *
     *        Special routes:
     *        - *index match index route.
     *        - *error match 404 and other errors.
     *
     *        Types:
     *        - (@see self::$s_type)
     *        - costume regular expression, for example: <var:#[a-e]{2}#>
     *
     * @param string $priority
     *        Specify priority, following priorities are available:
     *        `high`, `medium`, `low`.
     *
     * @param boolean $top
     *        If true, route will be prepended rather than appended to the
     *        given priority list.
     * --
     * @throws mysli\toolkit\exception\route 10 Invalid category.
     * --
     * @return boolean
     */
    static function add($call, $method, $route, $priority='medium', $top=false)
    {
        if (!in_array($priority, static::$r_options['categories']))
        {
            throw new exception\route("Invalid category: `{$category}`", 10);
        }

        $a_route = [
            'route'    => $route,
            'call'     => $call,
            'priority' => $priority
        ];

        if ($top)
        {
            array_unshift(static::$r[$priority], $a_route);
        }
        else
        {
            static::$r[$priority][] = $a_route;
        }

        return true;
    }

    /**
     * Remove particular route.
     * --
     * @param string $call
     *        Call method is used as an unique ID.
     *        You're allowed to use `*` to remove multiple routes, e.g.:
     *        `vendor.package::*`
     * --
     * @return integer  Number of modified routes.
     */
    static function remove($call)
    {
        $r_call = preg_quote($call, '#');
        $r_call = str_replace('\*', '.*?', $call);
        $r_call = '#'.$call.'#';
        $mod = 0;

        foreach (static::$r as $priority => $routes)
        {
            foreach ($routes as $i => $route)
            {
                if (preg_match($r_call, $route['call']))
                {
                    unset(static::$r[$priority][$i]);
                    $mod++;
                }
            }
        }

        return $mod;
    }

    /**
     * Update particular route.
     * --
     * @param string $call    Call method is used as an unique ID.
     * @param array  $options Available options: (@see self::add)
     * --
     * @return boolean
     */
    static function update($call, $options)
    {
        list($priority, $position, $route) = static::get($call);

        if (!$route)
        {
            return false;
        }

        if ($options['priority'] !== $priority)
        {
            $options = array_merge($route, $options);

            if (array_key_exists('top', $options))
            {
                $top = $options['top'];
                unset($options['top']);
            }
            else
            {
                $top = false;
            }

            static::remove($call);
            static::add(
                $options['call'],
                $options['method'],
                $options['route'],
                $options['priority'],
                $options['top']
            );
        }
        else
        {
            unset($route['method']);

            $options = array_merge($route, $options);

            if (isset($options['resolved']))
            {
                unset($options['resolved']);
            }

            static::$r[$priority][$position] = $options;
        }

        return true;
    }

    /**
     * Get route by $call.
     * --
     * @param string $call
     * --
     * @return array [
     *         string  $priority,
     *         integer $current_position,
     *         array   $route
     * ]
     */
    static function get($call)
    {
        foreach (static::$r as $priority => $routes)
        {
            foreach ($routes as $i => $route)
            {
                if ($call === $route['call'])
                {
                    return [$priority, $i, $route];
                }
            }
        }

        return [ null, null, null ];
    }

    /**
     * Convert a route to an URL.
     * --
     * @param string $call
     * @param array  $parameters
     * --
     * @throws mysli\toolkit\exception\route 10 Segment not found.
     * @throws mysli\toolkit\exception\route 20 Parameter not found.
     * @throws mysli\toolkit\exception\route 30 Parameter value is invalid.
     * --
     * @return string
     */
    static function to_url($call, array $parameters=[])
    {
        list($_, $_, $route) = static::get($call);

        if (!$route)
        {
            return null;
        }

        list($sroute, $segments) = static::extract_segments($route['route']);

        $final = [];
        $sroute = preg_split('/(?<!\\)\//', $sroute);

        foreach ($sroute as $rseg)
        {
            if (preg_match('/___"SEGMENT\:([a-z0-9_]+)"___/', $rseg, $m))
            {
                $segid = $m[1];

                if (!isset($segments[$segid]))
                {
                    throw new exception\route(
                        "Segment not found: `{$segid}`", 10);
                }

                if (!isset($parameters[$segid]))
                {
                    if ($segments[$segid][0]) // is_optional
                    {
                        break;
                    }
                    else
                    {
                        throw new exception\route(
                            "Parameter not found: `{$segid}`", 20);
                    }
                }

                if (!preg_match($segments[$segid][1], strval($parameters[$segid])))
                {
                    throw new exception\route(
                        "Parameter value is invalid: `{$parameters[$segid]}`, ".
                        "Expected: `{$segments[$segid][1]}`.",
                        30
                    );
                }

                $rseg = str_replace(
                    "___\"SEGMENT\:{$segid}\"___", $parameters[$segid], $rseg
                );
            }

            // Add segment to the list
            $final[] = $rseg;
        }

        return implode('/', $final);
    }

    /**
     * Reload routes.
     * --
     * @return boolean
     */
    static function reload()
    {
        static::$r = clist::decode_file(static::$r_file, static::$r_options);
        return is_array(static::$r);
    }

    /**
     * Write routes to file.
     * --
     * @return boolean
     */
    static function write()
    {
        return clist::encode_file(
            static::$r_file,
            static::$r,
            static::$r_options
        );
    }

    /**
     * Take route, return regex.
     * --
     * @param string $route
     * --
     * @throws mysli\toolkit\exception\route 10 Segment not found.
     * --
     * @return string
     */
    protected static function resolve_route($route)
    {
        if (substr($route, 0, 1) === '*')
        {
            return '*';
        }

        $final = [];

        list($sroute, $segments) = static::extract_segments($route);

        $sroute = preg_split('/(?<!\\)\//', $sroute);
        $g_optional = false;

        foreach ($sroute as $rseg)
        {
            $rseg = preg_quote($rseg, '#');

            if (preg_match('/___"SEGMENT\:([a-z0-9_]+)"___/', $rseg, $m))
            {
                $segid = $m[1];

                if (!isset($segments[$segid]))
                {
                    throw new exception\route(
                        "Segment not found: `{$segid}`", 10);
                }

                $rseg = str_replace(
                    "___\"SEGMENT\:{$segid}\"___",
                    "(?'{$segid}'{$segments[$segid][1]})",
                    $rseg
                );

                // If one is optional,
                // then from this point on, all will be optional
                if ($segments[$segid][0])
                {
                    $g_optional = true;
                }
            }

            // Finally add it to the list
            if ($g_optional)
            {
                $final[] = "?(?>{$rseg})?";
            }
            else
            {
                $final[] = $rseg;
            }
        }

        return implode('/', $final);
    }

    /**
     * Extract segments from route!
     * --
     * @param string $route
     * --
     * @throws mysli\toolkit\exception\route 10 Invalid REGEX in route.
     * --
     * @return array
     *         return [ string $route, array $segments ]
     *         $segments: [
     *             segid => [ boolean $is_optional, string $regex ]
     *         ]
     */
    protected static function extract_segments($route)
    {
        $segments = [];

        $route = preg_replace_callback(
        '/\<([a-z0-9_]+)(\??)\:(.*?)(?<!\\)\>/',
        function ($m) use (&$segments)
        {
            list($_, $segid, $is_optional, $regex) = $m;

            if (isset(static::$s_type[$regex]))
            {
                $regex = static::$s_type[$regex];
            }
            else if (substr($regex, 0, 1) === '#' && substr($regex, -1) ===  '#')
            {
                $regex = substr($regex, 1, -1);
            }
            else
            {
                throw new exception\route(
                    "Invalid REGEX in route: `{$regex}`", 10
                );
            }

            $segments[$segid] = [$is_optional, $regex];

            return '___"SEGMENT/'.$segid.'"___';
        }, $route);


        return [ $route, $segments ];
    }
}
