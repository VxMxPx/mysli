<?php

namespace mysli\toolkit; class cookie
{
    const __use = '
        .{
            type.arr -> arr,
            config,
            log,
            cypt,
            signature,
            exception.cookie
        }
    ';

    /**
     * Properties of current cookie instance.
     * --
     * @var array
     */
    private $properties = [
        'name'        => null,
        'value'       => null,
        'expire'      => 0,
        'path'        => '/',
        'domain'      => null,
        'secure'      => false,
        'httponly'    => false,
        'encrypt'     => null,
        'encrypt_key' => null,
        'sign'        => null,
        'sign_key'    => null
    ];

    /**
     * Cookie instance.
     * --
     * @param string $name
     */
    function __construct($name)
    {
        $this->set_name($name);

        /*
        Set default encryption parameters
         */
        $this->set_encrypt(null);
        $this->set_encrypt_key(null);

        /*
        Set default sign parameters
         */
        $this->set_signature(null);
        $this->set_signature_key(null);
    }

    /**
     * Set cookie name.
     * Will apply prefix if any set in configuration.
     * --
     * @param string $name
     * @param string $prefix
     *        Prefix cookie, if null prefix will be read from configuration.
     */
    function set_name($name, $prefix=null)
    {
        if ($prefix === null)
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

    /**
     * Set cookie's encryption.
     * You can set it to `null` which will mean that global
     * settings from configuration will be used.
     * --
     * @param boolean $encrypt
     */
    function set_encrypt($encrypt)
    {
        if ($encrypt === null)
            $encrypt = $c->get('cookie.encrypt', false);

        $this->properties['encrypt'] = $encrypt;
    }

    /**
     * Get current encrypt setting.
     * --
     * @return boolean
     */
    function get_encrypt()
    {
        return $this->properties['encrypt'];
    }

    /**
     * Set cookie's encryption key.
     * You can set it to `null` which will mean that global
     * settings from configuration will be used.
     * --
     * @param string $key
     */
    function set_encrypt_key($key)
    {
        if ($key === null)
            $key = $c->get('cookie.encrypt_key');

        $this->properties['encrypt_key'] = $key;
    }

    /**
     * Get current encrypt key setting.
     * --
     * @return string
     */
    function get_encrypt_key()
    {
        return $this->properties['encrypt_key'];
    }

    /**
     * Set cookie's signature.
     * You can set it to `null` which will mean that global
     * setting from configuration will be used.
     * --
     * @param boolean $sign
     */
    function set_signature($sign)
    {
        if ($sign === null)
            $sign = $c->get('cookie.sign', false);

        $this->properties['sign'] = $sign;
    }

    /**
     * Get current signature setting.
     * --
     * @return boolean
     */
    function get_signature()
    {
        return $this->properties['sign'];
    }

    /**
     * Set cookie's signature key.
     * You can set it to `null` which will mean that global
     * setting from configuration will be used.
     * --
     * @param string $key
     */
    function set_signature_key($key)
    {
        if ($key === null)
            $key = $c->get('cookie.sign_key');

        $this->properties['sign_key'] = $key;
    }

    /**
     * Get current signature key setting.
     * --
     * @return string
     */
    function get_signature_key()
    {
        return $this->properties['sign_key'];
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
     * @throws exception\cookie
     *         10 If encryption is set to true, but there's no encryption key set.
     *
     * @throws exception\cookie
     *         20 If signature is set to true, but there's no signature key set.
     * --
     * @return boolean
     */
    static function set($name, $value=null, $expire=0)
    {
        $c = config::select('mysli.toolkit');

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

        /*
        Check if cookie actually needs to be encrypted, and encrypt it.
         */
        if ($cookie->get_encrypt())
        {
            if (!$cookie->get_encrypt_key())
                throw new exception\cookie(
                    "Cookie encrypt requires `cookie.encrypt_key` ".
                    "to be set in configuration.", 1
                );

            $cookie->set_value(
                crypt::encrypt($cookie->get_value(), $cookie->get_encrypt_key())
            );
        }

        /*
        Check if cookie needs to be signed, and signed it.
         */
        if ($cookie->get_signature())
        {
            if (!$cookie->get_signature_key())
                throw new exception\cookie(
                    "Cookie sign requires `cookie.sign_key` ".
                    "to be set in configuration.", 2
                );

            $cookie->set_value(
                signature::create($cookie->get_value(), $cookie->get_signature_key())
            );
        }

        \log::info("Set: `{$cookie->get_name()}`.", __CLASS__);

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
     *        - string (one cookie),
     *        - array (multiple cookies),
     *        - \mysli\toolkit\cookie an instance of cookie,
     *          with advanced settings, like signature, etc...
     * --
     * @throws exception\cookie
     *         10 If signature is required but not present in value.
     *
     * @throws exception\cookie
     *         20 If signature is invalid.
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
                $cookies[] = static::get($val, $default);

            return $cookies;
        }

        /*
        Getting one cookie from object.
         */
        if (is_object($name) && is_a($name, __CLASS__))
        {
            $cookie = $name;

            if (arr::key_in($_COOKIE, $cookie->get_name()))
                $value = $_COOKIE[$name];
            else
                return $default;

            /*
            Resolve signature.
             */
            if ($cookie->get_signature())
            {
                if (!signature::has($value))
                    throw new exception\cookie(
                        "Expected cookie with a signature, got unsigned.", 10
                    );

                if (!signature::is_valid($value, $cookie->get_signature_key()))
                    throw new exception\cookie(
                        "Cookie's signature is invalid.", 20
                    );

                $value = signature::strip($value);
            }

            /*
            Resolve encryption.
             */
            if ($cookie->get_encrypt())
            {
                $value = crypt::decrypt($value, $cookie->get_encrypt_key());
            }

            return $value;
        }


        /*
        Get one cookie outside object context.
         */
        $name = config::select('mysli.toolkit', 'cookie.prefix', '').$name;

        if (arr::key_in($_COOKIE, $name))
            return $_COOKIE[$name];
        else
            return $default;
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

        \log::info("Remove: `{$name}`.", __CLASS__);

        return setcookie(
            $name,
            '',
            time() - 3600,
            $path,
            $domain
        );
    }
}
