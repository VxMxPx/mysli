<?php

namespace mysli\core {
    class inject {
        private $namespace;
        /**
         * Instance of injector
         * @param string $namespace
         */
        function __construct($namespace) {
            $this->namespace = $namespace;
        }
        /**
         * From hich package and as what we're importing class
         * @param  string $package
         * @param  string $as
         * @return null
         */
        function from($package, $as=null) {
            $package = explode('/', $package);
            if (count($package) === 2) {
                $package[] = $package[1];
            }
            if (!$as) {
                $as = $package[count($package)-1];
            }
            $class = implode('\\', $package);
            $as = $this->namespace . '\\' . str_replace('/', '\\', $as);
            class_alias($class, $as);
        }
        /**
         * Inject classes to the particular namespace
         * @param  string $namespace
         * @return mysli\core\inject
         */
        static function to($namespace) {
            return new self($namespace);
        }
    }
}
