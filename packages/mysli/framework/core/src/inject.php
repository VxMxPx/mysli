<?php

namespace mysli\framework\core {
    class inject {

        private $vendor;
        private $package;
        private $namespace;
        private $prefix = '';

        /**
         * Instance of injector
         * @param string $namespace
         */
        function __construct($namespace) {
            if (!$namespace) {
                throw new \Exception("Invalid namespace.", 1);
            }
            $this->namespace = $namespace;
            $segments = explode('\\', $namespace);
            if (file_exists(
                MYSLI_PKGPATH . '/' .
                implode('/', array_slice($segments, 0, 2)) . '/' .
                'mysli.pkg.ym')) {
                $this->package = implode('/', array_slice($segments, 0, 2));
                $this->vendor  = implode('/', array_slice($segments, 0, 1));
            } else {
                $this->package = implode('/', array_slice($segments, 0, 3));
                $this->vendor  = implode('/', array_slice($segments, 0, 2));
            }
        }
        /**
         * Set prefix to load packages from
         * @param  string $string
         * @return $this
         */
        function prefix($string) {
            $this->prefix = trim($string, '/') . '/';
            return $this;
        }
        /**
         * From hich package and as what we're importing class
         * @param  string $package
         * @param  string $as
         * @return $this
         */
        function from($package, $as=null) {
            if (substr($package, 0, 3) === '../') {
                $package = $this->vendor . '/' . substr($package, 3);
            } elseif (substr($package, 0, 2) === './') {
                $package = $this->package . '/' . substr($package, 2);
            }
            // Set prefix
            $package = $this->prefix . $package;
            // Prepeare meaningful segments
            $segments = explode('/', $package);
            // Check weather is meta
            $is_meta = !file_exists(
                MYSLI_PKGPATH . '/' .
                implode('/', array_slice($segments, 0, 2)) . '/' .
                'mysli.pkg.ym'
            );
            $cut = $is_meta ? 3 : 2;
            // If only 2 segments, then we're in index class e.g.:
            // vendor/pkg => vendor/pkg/pkg
            if (count($segments) === $cut) {
                $segments[] = $segments[$cut-1];
            }
            $pkg = implode('/', array_slice($segments, 0, $cut));
            $ns = implode('/', array_slice($segments, $cut, -1));
            $last = array_slice($segments, -1)[0];

            // folder insertion
            if ($last === '*') {
                $ns = $ns ? "{$ns}/" : '';
                foreach (scandir(MYSLI_PKGPATH."/{$pkg}/src/{$ns}") as $file) {
                    if (substr($file, -4) === '.php') {
                        $this->from("{$pkg}/{$ns}".substr($file, 0, -4), $as);
                    }
                }
                return $this;
            }

            // multiple insersion
            if (strpos($last, ',')) {
                if (strpos($as, ',')) {
                    $as = explode(',', $as);
                }
                $last = explode(',', trim($last, '{}'));
                if (is_array($as) && (count($as) !== count($last))) {
                    throw new \Exception(
                        "Wrong number of segments in `\$as`.!", 1);
                }
                $ns = $ns ? "{$ns}/" : '';
                foreach ($last as $lpos => $last_segment) {
                    $last_segment = trim($last_segment);
                    $this->from("{$pkg}/{$ns}{$last_segment}",
                        (is_array($as) ? $as[$lpos] : $as));
                }
                return $this;
            }

            // if there's no as, we'll define it to be the same as last package
            if (!$as) {
                $as = $last;
            }
            if (strpos($as, '%s')) {
                $as = sprintf($as, $last);
            }
            $as = str_replace('/', '\\', $as);
            $as = $this->namespace . '\\' . $as;
            $class = str_replace('/', '\\', $package);
            if (!class_exists($as, false)) {
                class_alias($class, $as);
            }
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
