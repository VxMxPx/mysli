<?php

namespace mysli\cookie {

    inject::to(__NAMESPACE__)
        ->from('mysli/html')
        ->from('mysli/arr')
        ->from('mysli/time')
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
         * @param  string $expire false for default expire time set in
         * configuration enter value, actual value must be time::now() + seconds
         * @return boolean
         */
        static function set($name, $value, $path='/', $expire=false) {
            $config = config::select('mysli/cookie');
            if ($expire === false) {
                $expire = time::now() + $config->get('timeout');
            }
            $domain = $config->get('domain', $_SERVER['SERVER_NAME']);
            $prefix = $config->get('prefix');
            return setcookie($prefix . $name, $value, $expire, $path, $domain);
        }
        /**
         * Get cookie by name.
         * @param  mixed   $key string (one cookie), array (multiple cookies)
         * @param  boolean $clean
         * @return string
         */
        static function get($key, $default=null, $clean=true) {
            if (is_array($key)) {
                $cookies = [];
                foreach ($key as $val) {
                    $cookies[] = self::get($val, $default, $clean);
                }
                return $cookies;
            }
            $key = config::select('mysli/cookie', 'prefix') . $key;
            if (arr::key_in($_COOKIE, $key)) {
                // TODO: better name
                return $clean
                    ? html::special_chars($_COOKIE[$key])
                    : $_COOKIE[$key];
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
        public function remove($name, $path='/') {
            $domain = config::select(
                'mysli/cookie', 'domain', $_SERVER['SERVER_NAME']);
            $prefix = config::select('mysli/cookie', 'prefix');
            return setcookie(
                $prefix . $name, '', time() - 3600, $path, $domain);
        }
    }
}
