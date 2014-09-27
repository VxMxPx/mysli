<?php

namespace mysli\dev\phpt {

    __use(__namespace__, [
        './test_case',
        'mysli/framework' => [
            'fs/{fs,file,dir}',
            'exception/*' => 'framework/exception/%s',
        ]
    ]);

    class collection implements \Countable, \Iterator {

        private $position = 0;
        private $tests    = [];

        /**
         * Check all test in particular path. Path must be full absolute path,
         * directory must exists. Filename can use '*' to match more (or all)
         * files. For example:
         * /home/me/path/*.phpt | /home/me/path/*_basic.phpt ...
         * @param string $path
         */
        function __construct($path) {
            $dir   = dirname($path);
            $file = file::name($path);
            if (!dir::exists($dir)) {
                throw new framework\exception\not_found(
                    "Directory not found: `{$dir}`", 1);
            }
            if (strpos($file, '*') !== false) {
                $file = preg_quote($file);
                $file = str_replace("\\*", '.*?', $file);
                $file = "/{$file}/";
                $regex = true;
            } else $regex = false;

            $this->mk_tests($dir, $file, $regex);
        }
        /**
         * Get those tests which were executed successfully.
         * @return array
         */
        function success() {
            $r = [];
            foreach ($this->tests as $t)
                $t->executed() && $t->succeed() && ($r[] = $t);
            return $r;
        }
        /**
         * Get those tests which failed.
         * @return array
         */
        function failed() {
            $r = [];
            foreach ($this->tests as $t)
                $t->executed() && !$t->succeed() && ($r[] = $t);
            return $r;
        }
        /**
         * Get skipped tests.
         * @return array
         */
        function skipped() {
            $r = [];
            foreach ($this->tests as $t)
                $t->executed() && $t->skipped() && ($r[] = $t);
            return $r;
        }
        /**
         * Get those tests which were actually executed.
         * @return array
         */
        function executed() {
            $r = [];
            foreach ($this->tests as $t)
                $t->executed() && ($r[] = $t);
            return $r;
        }
        /**
         * Get those tests which were NOT executed.
         * @return array
         */
        function not_executed() {
            $r = [];
            foreach ($this->tests as $t)
                !$t->executed() && ($r[] = $t);
            return $r;
        }
        /**
         * Get sum of run time of all tests.
         * @return float
         */
        function run_time() {
            $s = 0.0;
            foreach ($this->tests as $t)
                $t->executed() && ($s += $t->run_time());
            return $s;
        }
        /**
         * Countable: get number of tests.
         * @return integer
         */
        function count() {
            return count($this->tests);
        }
        /**
         * Rewind the Iterator to the first element.
         * @return null
         */
        function rewind() {
            $this->position = 0;
        }
        /**
         * Return the current element.
         * @return mixed string (test filename) | object (phpt\test_case)
         */
        function current() {
            return $this->tests[$this->position];
        }
        /**
         * Return the current key.
         * @return string test filename
         */
        function key() {
            return $this->current()->filename();
        }
        /**
         * Move forward to the next element.
         * @return null
         */
        function next() {
            ++$this->position;
        }
        /**
         * Check if current position is valid.
         * @return boolean
         */
        function valid() {
            return isset($this->tests[$this->position]);
        }

        /**
         * Find tests in directory and construct them.
         * @param  string $dir
         * @param  string $filep
         * @param  string $regex
         * @return null
         */
        private function mk_tests($dir, $filep, $regex) {
            foreach (fs::ls($dir) as $file) {
                if (dir::exists(fs::ds($dir, $file))) {
                    $this->mk_tests(fs::ds($dir, $file), $filep, $regex);
                    continue;
                }
                if ($regex) {
                    if (!preg_match($filep, $file)) {
                        continue;
                    }
                } elseif ($file != $filep) {
                    continue;
                }
                if (substr($file, -5) !== '.phpt') {
                    continue;
                }
                $this->tests[] = new test_case(fs::ds($dir, $file));
            }
        }
    }
}
