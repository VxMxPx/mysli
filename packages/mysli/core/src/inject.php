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
            // Prepeare meaningful segments
            $segments = explode('/', $package);
            // If only 2 segments, then we're in index class e.g.:
            // vendor/pkg => vendor/pkg/pkg
            if (count($segments) === 2) { $segments[] = $segments[1]; }
            $pkg = implode('/', array_slice($segments, 0, 2));
            $ns = implode('/', array_slice($segments, 2, -1));
            $last = array_slice($segments, -1)[0];

            // folder insertion
            if ($last === '*') {
                foreach (scandir(MYSLI_PKGPATH."/{$pkg}/src/{$ns}") as $file) {
                    if (substr($file, -4) === '.php') {
                        $this->from(
                            "{$pkg}/{$ns}/" . substr($file, 0, -4), $as);
                    }
                }
                return $this;
            }

            // multiple insersion
            if (strpos($last, ',') !== false) {
                if (strpos($as, ',')) { $as = explode(',', $as); }
                $last = explode(',', trim($last, '{}'));
                if (is_array($as) && (count($as) !== count($last))) {
                    throw new exception\argument(
                        "Wrong number of segments in `\$as`.!", 1);
                }
                foreach ($last as $lpos => $last_segment) {
                    $last_segment = trim($last_segment);
                    $this->from("{$pkg}/{$ns}/{$last_segment}",
                        (is_array($as[$lpos]) ? $as[$lpos] : $as));
                }
                return $this;
            }

            // if there's no as, we'll define it to be the same as last package
            if (!$as) {
                $as = $last;
            }

            if (is_integer($as)) {
                $as = implode('\\', array_slice($segments, -($as)));
            } else {
                $as = str_replace('/', '\\', $as);
            }
            $class = str_replace('/', '\\', $package);
            $as = $this->namespace . '\\' . $as;
            class_alias($class, $as);
            return $this;
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
