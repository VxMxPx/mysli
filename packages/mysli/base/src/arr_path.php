<?php

namespace mysli\base {
    class arr_path {
        /**
         * Get array value by path
         * array   => ['user' => ['address' => 'My Address']]
         * path    => user/address
         * return  => My Address
         * @param  array  $array
         * @param  string $path
         * @param  mixed  $default
         * @return mixed
         */
        static function get(array $array, $path, $default=null) {
            tc::need_str($path);

            $path = trim($path, '/');
            $path = str::split($path, '/');
            $get  = $array;

            foreach ($path as $w) {
                if (arr::key_in($get, $w) && !is_null($get[$w])) {
                    $get = $get[$w];
                } else {
                    return $default;
                }
            }

            return $get;
        }
        /**
         * Set array value by path
         * array  => ['user' => ['address' => 'My Address']]
         * path   => user/address
         * value  => 'New Address'
         * result => ['user' => ['address' => 'New Address']]
         * @param  array  $array
         * @param  string $path
         * @param  mixed  $value
         * @return null
         */
        static function set(array &$array, $path, $value) {
            tc::need_str($path);
            $what = trim($path, '/');
            $what = str::split($what, '/');
            $previous = $value;
            $new = [];

            for ($i=count($what); $i--; $i==0) {
                $w = $what[$i];
                $new[$w]  = $previous;
                $previous = $new;
                $new = [];
            }

            $array = arr::merge($array, $previous);
        }
        /**
         * Delete array value by path
         * array  => ['user' => ['address' => 'My Address']]
         * path   => user/address
         * result => ['user' => []]
         * @param  array  $array
         * @param  string $path
         * @return null
         */
        static function delete(array &$array, $path) {
            tc::need_str($path);
            $array = self::delete_helper($array, $path, null);
        }
        /**
         * Delete by path helper
         * @param  array  $array
         * @param  string $path
         * @param  string $cp
         * @return array
         */
        protected static function delete_helper(array $array, $path, $cp) {
            $result = [];

            foreach ($array as $k => $i) {
                $cup = $cp . '/' . $k;
                if (str::trim($cup, '/') === str::trim($path,'/')) {
                    continue;
                }
                if (is_array($i)) {
                    $result[$k] = self::delete_helper($i, $path, $cup);
                } else {
                    $result[$k] = $i;
                }
            }

            return $result;
        }
    }
}
