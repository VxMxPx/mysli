<?php

/**
 * # Route
 *
 * Define a new route to be used by the system.
 */
namespace mysli\toolkit\router; class route
{
    /**
     * Set to which request method this route will respond. (@see self::method())
     */
    const get    = 'GET';
    const post   = 'POST';
    const put    = 'PUT';
    const delete = 'DELETE';
    const all    = 'ALL';

    /**
     * Route's type. (@see self::type())
     */
    const before  = '@BEFORE';
    const after   = '@AFTER';
    const special = '@SPECIAL';
    const high    = '@HIGH';
    const normal  = '@NORMAL';
    const low     = '@LOW';

    /**
     * What URI this route will match.
     * For example: "tag/{tag}/{page}"
     * --
     * @var string
     */
    private $match;

    /**
     * Regular expression representation of user's set `match`.
     * (@see self::match())
     * --
     * @var string
     */
    private $match_regex;

    /**
     * List of parameters forwarded by this route.
     * --
     * @var array
     */
    private $parameters = [];

    /**
     * To which vendor.package.class::method this route will be forwarded.
     * --
     * @var string
     */
    private $to;

    /**
     * Method which this route will respond to.
     * --
     * @var string
     */
    private $method = ['GET'];

    /**
     * Route's prefix. (@see self::prefix())
     * --
     * @var string
     */
    private $prefix;

    /**
     * Type of route. (@see self::type())
     * --
     * @var string
     */
    private $type = '@NORMAL';

    /**
     * Route's constructor.
     * --
     * @param  string $match (@see self::match())
     * @param  string $to    (@see self::to())
     */
    function __constructor($match, $to)
    {
        $this->match($match);
        $this->to($to);
    }

    /**
     * A request method to which this route respond.
     * The available methods are:
     * self::get, self::post, self::delete, self::put, self::arr
     * --
     * @param mixed $method Either one method, for example self::get,
     *                      Or multiple, as an array: [ self::post, self::put ]
     * --
     * @throws mysli\toolkit\exception\route 10 Invalid type of method.
     * --
     * @return $this
     */
    function method($method)
    {
        if ($method === self::all)
            $this->method = [ self::get, self::post, self::put, self::delete ];
        elseif (is_string($method))
            $this->method = [ $method ];
        elseif (is_array($method))
            $this->method = $method;
        else
            throw new exception\route("Invalid type of method.", 10);
    }

    /**
     * An URI to be matched by this route.
     * Enter a route as a regular string, but put parameters in curly brackets,
     * for example: `tag/{tag}/{page}`.
     * This will either modify existing parameters, or define new.
     * The `$this->parameter()` method can be used to to specify details of
     * each parameter.
     * Parameters will be sent to method defined in by `$this->to()`, in
     * such order as defined here.
     * Do (@see self::to()) and (@see self::parameter()) for more information.
     * --
     * @param string $uri Format, for example path/{param1}/path/{param2}/{param3}
     * --
     * @return $this
     */
    function match($uri)
    {

    }

    function prefix() {}
    function type() {}
    function to() {}
    function parameter() {}
    function save() {}
    function remove() {}
}
