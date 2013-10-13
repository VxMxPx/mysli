<?php

namespace Mysli\Core\Lib;

class Cookie
{
    /**
     * Create cookie
     * --
     * @param  string $name
     * @param  string $value
     * @param  string $expire  Use false for default expire time (set in
     *                         configuration), or enter value (actuall value so
     *                         must be time() + seconds)
     * --
     * @return bool
     */
    public static function create($name, $value, $expire=false)
    {
        // Expire
        if ($expire === false) {
            $expire = time() + Cfg::get('cookie/timeout');
        }

        // Domain
        $domain = Cfg::get('cookie/domain');
        if (!$domain) { $domain = $_SERVER['SERVER_NAME']; }

        Log::info('Cookie will be set, as: "' . Cfg::get('cookie/prefix') .
                    $name . '", with value: "'  .
                    $value . '", set to expire: "' .
                    ($expire) . '" to domain: "' . $domain . '"',
                    __FILE__, __LINE__);

        return setcookie(
            Cfg::get('cookie/prefix') . $name,
            $value,
            $expire,
            "/",
            $domain);
    }

    /**
     * Fetch an item from the COOKIE array
     * --
     * @param  string $key
     * --
     * @return mixed
     */
    public static function read($key='')
    {
        $key_prefix = Cfg::get('cookie/prefix') . $key;

        // Is Cookie With Prefix Set?
        if (isset($_COOKIE[$key_prefix]))
            { $return = $_COOKIE[$key_prefix]; }
        elseif (isset($_COOKIE[$key]))
            { $return = $_COOKIE[$key]; }
        else
            { return false; }

        return htmlspecialchars($return);
    }

    /**
     * Remove cookie
     * --
     * @param  string $name
     * --
     * @return boolean
     */
    public static function remove($name)
    {
       Log::info('Cookie will be unset: `'.Cfg::get('cookie/prefix').$name.'`.', __FILE__, __LINE__);

        // Domain
        $domain = Cfg::get('cookie/domain');
        if (!$domain) {
            $domain = $_SERVER['SERVER_NAME'];
        }

       return setcookie(
            Cfg::get('cookie/prefix') . $name,
            '',
            time() - 3600,
            "/",
            $domain);
    }
}