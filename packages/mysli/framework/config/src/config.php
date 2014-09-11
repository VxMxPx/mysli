<?php

namespace mysli\framework\config {

    __use(__namespace__,
        '../fs/{fs,file}',
        '../json',
        '../type/{arr,arr_path}'
    );

    class config {

        static private $registry;

        private $package;
        private $filename;
        private $data;
        private $cache;

        /**
         * Config data
         * @param string $package vendor/package
         */
        function __construct($package) {
            $this->package = $package;
            $this->filename = fs::datpath('mysli.config', $package . '.json');

            // If we have file, then load contents...
            if (file::exists($this->filename)) {
                $this->data = json::decode_file($this->filename);
            }
        }
        /**
         * Get config element, by: sub/key/main
         * @param  mixed $key string (sub/key) or array ([sub/key, sub/key2])
         * @param  mixed $default value if key not found
         * @return mixed
         */
        function get($key, $default=null) {
            // If key is an array, do recursive search.
            if (arr::valid($key)) {
                $values = [];
                foreach ($key as $val) {
                    $values[] = $this->get($val, $default);
                }
                return $values;
            }

            if (arr::get($this->cache, $key)) {
                return $this->cache[$key];
            }

            $value = arr_path::get($key, $this->config, $default);

            // We cache only when we assume it's not default value...
            if ($value !== $default) {
                $this->cache[$key] = $value;
            }

            // Return value in any case...
            return $value;
        }
        /**
         * Set value for key.
         * @param string $path sub/key
         * @param mixed  $value
         * @return null
         */
        function set($path, $value) {
            // Clear cache to avoid corrupted data
            $this->cache = [];
            return arr_path::set($path, $value, $this->config);
        }
        /**
         * Append config to the file.
         * @param  array  $config
         * @return null
         */
        function merge(array $config) {
            $this->cache = [];
            $this->config = arr::merge($this->config, $config);
        }
        /**
         * Save config file.
         * @return boolean
         */
        function save() {
            return json::encode_file($this->filename, $this->data);
        }
        /**
         * Delete config file.
         * @return boolean
         */
        function destroy() {
            $this->cache = $this->config = [];
            unset(self::$registry[$this->package]);
            if (file::exists($this->filename)) {
                return file::remove($this->filename);
            } else {
                return null;
            }
        }

        /**
         * Get config instance or value.
         * @param  string $package vendor/package
         * @param  mixed  $key
         * @param  mixed  $default
         * @return mixed
         */
        static function select($package, $key=false, $default=null) {
            if (!arr::get($registry, $package)) {
                self::$registry[$package] = new self($package);
            }
            $config = self::$registry[$package];
            if ($key) {
                return $config->get($key, $default);
            } else {
                return $config;
            }
        }
    }
}
