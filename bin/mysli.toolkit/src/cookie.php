<?php

/**
 * # Cookies
 *
 * A simple Cookies management.
 *
 * ## Setting
 *
 * A cookie can be set, by calling a static method `set`, for example:
 *
 *      cookie::set('name', 'cookie-value', $expiration);
 *
 * If you need to set an advanced options, like domain and path, then a new
 * instance of this class can be constructed:
 *
 *      $cookie = new cookie('name');
 *      $cookie->set_path('/foo/');
 *      $cookie->set_expire(time() + 60*60*24*4);
 *
 *      // Set cookie with a static method:
 *      cookie::set($cookie);
 *
 * ## Getting
 *
 * A cookie can be acquired by calling a static method `get`, for example:
 *
 *      cookie::get('name');
 *
 * A default value can be provided if cookie was not found:
 *
 *      cookie::get('name', 'default-value');
 *
 * ## Removing
 *
 * Cookie can be removed by calling a static method `delete`, for example:
 *
 *      cookie::remove('name');
 *
 * Path and domain can be send along:
 *
 *      cookie::remove('name', '/foo/', 'blog.domain.tld');
 *
 * ## Configuration
 *
 * **PREFIX**\\
 * Gives an unique prefix to all cookies set by this application.
 *
 *     string mysli.toolkit, cookie.prefix ['']
 */
namespace mysli\toolkit; class cookie
{
    const __use = '.{ type.arr, config }';

    /**
     * Properties of current cookie instance.
     * --
     * @var array
     */
    private $properties = [
        'name'     => null,
        'value'    => null,
        'expire'   => 0,
        'path'     => '/',
        'domain'   => null,
        'secure'   => false,
        'httponly' => false
    ];

    /**
     * Cookie instance.
     * --
     * @param string $name
     */
    function __construct($name)
    {
        $this->set_name($name);
    }

    /**
     * Set cookie name.
     * Will apply prefix if any set in configuration.
     * --
     * @param string $name
     */
    function set_name($name)
    {
        $prefix = config::select('mysli.toolkit', 'cookie.prefix', '');
        $this->properties['name'] = $prefix.$name;;
    }

    /**
     * Get cookie's name.
     * --
     * @return string
     */
    function get_name()
    {
        return $this->properties['name'];
    }

    /**
     * Set the value of the cookie.
     * --
     * @param string $value
     */
    function set_value($value)
    {
        $this->properties['value'] = $value;
    }

    /**
     * Get the value of the cookie.
     * --
     * @return string
     */
    function get_value()
    {
        return $this->properties['value'];
    }

    /**
     * The time the cookie expires as an Unix timestamp.
     * Use: `time()+60*60*24*30` will set cookie to expire in 30 days.
     * If set to zero, cookie will expire at the end of the session.
     * --
     * @param integer $time
     */
    function set_expire($time=0)
    {
        $this->properties['time'] = $time;
    }

    /**
     * Get the cookie expiration time.
     * --
     * @return integer
     */
    function get_expire()
    {
        return $this->properties['time'];
    }

    /**
     * Set path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available within the entire domain.
     * If set to '/foo/', the cookie will only be available within
     * the /foo/ directory and all sub-directories such as /foo/bar/ of domain.
     * --
     * @param string $path
     */
    function set_path($path)
    {
        $this->properties['path'] = $path;
    }

    /**
     * Get cookie's path on the server.
     * --
     * @return string
     */
    function get_path()
    {
        return $this->properties['path'];
    }

    /**
     * Set the domain that the cookie is available to. Setting the domain to
     * 'www.example.com' will make the cookie available in the www subdomain
     * and higher subdomains. Cookies available to a lower domain, such as
     * 'example.com' will be available to higher subdomains, such as
     * 'www.example.com'.
     * Older browsers still implementing the deprecated » RFC 2109 may
     * require a leading . to match all subdomains.
     * --
     * @param string $domain
     */
    function set_domain($domain)
    {
        $this->properties['domain'] = $domain;
    }

    /**
     * Get the cookie's domain.
     * --
     * @return string
     */
    function get_domain()
    {
        return $this->properties['domain'];
    }

    /**
     * Indicates that the cookie should only be transmitted over a secure
     * HTTPS connection from the client. When set to TRUE, the cookie
     * will only be set if a secure connection exists.
     * On the server-side, it's on the programmer to send this kind of
     * cookie only on secure connection
     * (e.g. with respect to $_SERVER["HTTPS"]).
     * --
     * @param boolean $secure
     */
    function set_secure($secure)
    {
        $this->properties['secure'] = !!$secure;
    }

    /**
     * Get cookie's secure setting.
     * --
     * @return boolean
     */
    function get_secure()
    {
        return $this->properties['secure'];
    }

    /**
     * When TRUE the cookie will be made accessible only through
     * the HTTP protocol. This means that the cookie won't be accessible
     * by scripting languages, such as JavaScript.
     * --
     * @param boolean $httponly
     */
    function set_httponly($httponly)
    {
        $this->properties['httponly'] = !!$httponly;
    }

    /**
     * Get cookie's httponly setting.
     * --
     * @return boolean
     */
    function get_httponly()
    {
        return $this->properties['httponly'];
    }

    /*
    --- Static -----------------------------------------------------------------
     */


    /**
     * Set a cookie.
     * --
     * @param mixed $name
     *        Either cookie's name, or an instance of mysli\toolkit\cookie,
     *        to set cookie with advanced options.
     *
     * @param string $value
     *        If you provided instance of self as a name,
     *        then value is not required but it will be set if provided.
     *
     * @param string $expire
     *        Need to be time() + seconds.
     *        If you provided instance of self as a name,
     *        then expire is not required and but will be set if provided.
     * --
     * @return boolean
     */
    static function set($name, $value=null, $expire=0)
    {
        if (is_object($name) && is_a($name, __CLASS__))
        {
            $cookie = $name;

            if ($value !== null)
                $cookie->set_value($value);

            if ($expire !== 0)
                $cookie->set_expire($expire);
        }
        else
        {
            $cookie = new self($name);
            $cookie->set_expire($expire);
            $cookie->set_value($value);
        }

        return setcookie(
            $cookie->get_name(),
            $cookie->get_value(),
            $cookie->get_expire(),
            $cookie->get_path(),
            $cookie->get_domain(),
            $cookie->get_secure(),
            $cookie->get_httponly()
        );
    }

    /**
     * Get cookie by a name.
     * --
     * @param mixed $name
     *        String (one cookie), array (multiple cookies)
     * --
     * @return string
     */
    static function get($name, $default=null)
    {
        /*
        Getting multiple cookies.
         */
        if (is_array($name))
        {
            $cookies = [];
            foreach ($name as $val)
            {
                $cookies[] = self::get($val, $default);
            }

            return $cookies;
        }

        /*
        Getting one cookie.
         */

        // Set prefix.
        $name = config::select('mysli.toolkit', 'cookie.prefix', '').$name;

        if (arr::key_in($_COOKIE, $name))
        {
            return $_COOKIE[$name];
        }
        else
        {
            return $default;
        }
    }

    /**
     * Removes a cookie.
     * --
     * @param string $name
     * @param string $path
     * @param string $domain
     * --
     * @return boolean
     */
    static function remove($name, $path='/', $domain=null)
    {
        $name = config::select('mysli.toolkit', 'cookie.prefix', '').$name;

        return setcookie(
            $name,
            '',
            time() - 3600,
            $path,
            $domain
        );
    }
}
