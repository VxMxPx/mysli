<?php

namespace mysli\cookie {

    \inject::to(__namespace__)
    ->from('mysli/core/type/arr')
    ->from('mysli/config');

    class cookie {
        /**
         * Set cookie.
         * @param string  $name
         * @param string  $value
         * @param  string $path on the server in which the cookie
         * will be available on. If set to '/', the cookie will be available
         * within the entire domain. If set to '/foo/', the cookie will only be
         * available within the /foo/ directory and all sub-directories
         * such as /foo/bar/ of domain.
         * @param  string $expire false for default expire time set in config
         * custom value - must be time() + seconds
         * @return boolean
         */
        static function set($name, $value, $path='/', $expire=false) {
            $config = config::select('mysli/cookie');
            if ($expire === false) {
                $expire = time() + $config->get('timeout');
            }
            $domain = $config->get('domain', $_SERVER['SERVER_NAME']);
            $prefix = $config->get('prefix');
            return setcookie($prefix . $name, $value, $expire, $path, $domain);
        }
        /**
         * Get cookie by name.
         * @param  mixed   $key string (one cookie), array (multiple cookies)
         * @return string
         */
        static function get($key, $default=null) {
            if (is_array($key)) {
                $cookies = [];
                foreach ($key as $val) {
                    $cookies[] = self::get($val, $default);
                }
                return $cookies;
            }
            $key = config::select('mysli/cookie', 'prefix') . $key;
            if (arr::key_in($_COOKIE, $key)) {
                return $_COOKIE[$key];
            } else {
                return $default;
            }
        }
        /**
         * Remove a cookie.
         * @param  string $name
         * @param  string $path
         * @return boolean
         */
        static function remove($name, $path='/') {
            $domain = config::select(
                'mysli/cookie', 'domain', $_SERVER['SERVER_NAME']);
            $prefix = config::select('mysli/cookie', 'prefix');
            return setcookie(
                $prefix . $name, '', time() - 3600, $path, $domain);
        }
    }
}
