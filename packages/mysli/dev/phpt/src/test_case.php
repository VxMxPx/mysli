<?php

namespace mysli\dev\phpt {

    __use(__namespace__, [
        './{engine,parser,diff}',
        'mysli/framework' => [
            'exception/*' => 'framework/exception/%s',
            'fs/{fs,file}'
        ]
    ]);

    class test_case {
        // false = not executed, -1 = skipped, 0 = failed, 1 = success
        private $status   = false;
        private $run_time = 0.0;
        private $skipif_message = '';
        private $parsed;
        private $filename;
        private $rel_filename;
        private $dir_temp;
        private $filename_temp;
        private $output;
        private $diff  = [];
        private $files = []; // created files

        /**
         * Create new test case.
         * @param string $filename
         */
        function __construct($filename) {
            if (!file::exists($filename)) {
                throw new framework\exception\not_found(
                    "File not found: `{$filename}`");
            }
            $this->filename = $filename;
            $this->dir_temp = fs::datpath('temp/phpt');
            $this->filename_temp = md5($this->filename);
        }
        /**
         * Execute this test.
         * @return integer (status)
         */
        function execute() {
            $st = microtime(true);

            // remove old files if any exists
            $this->cleanup();
            $this->output = '';
            $this->diff = [];
            $this->skipif_message = '';

            // parse phpt file
            $this->parsed = parser::process($this->filename);

            // get PHP location
            if (!$this->parsed['cgi']) {
                $php = exec('which php')     ?: '/usr/bin/env php';
            } else {
                $php = exec('which php-cgi') ?: '/usr/bin/env php-cgi';
            }
            $dir = dirname($this->filename);

            // see if we have input file
            if (isset($this->parsed['inputf'])) {
                $inputf = fs::ds(
                    "{$this->dir_temp}/{$this->filename_temp}.input");
                file::write($inputf, $this->parsed['inputf']);
                $this->files[] = $inputf;
                $in = "< \"{$inputf}\"";
            } else $in = '';

            // check if skipif is set
            if (isset($this->parsed['skipif'])) {
                $skipif = fs::ds(
                    "{$this->dir_temp}/{$this->filename_temp}.skipif.php");
                file::write($skipif, $this->parsed['skipif']);
                $this->files[] = $skipif;
                $command = implode(' ', [
                    $php, $this->parsed['inip'], '-f "' . $skipif . '"',
                    $this->parsed['args'], '2>&1', $in]);
                $r = engine::run($command, $this->parsed['env'], $dir);
                if(trim($r)) {
                    $this->skipif_message = $r;
                    $this->status = -1;
                    return -1;
                }
            }
            // virtuals?
            if (isset($this->parsed['virtual'])) {
                foreach ($this->parsed['virtual'] as $virtual) {
                    $filename = fs::ds($this->dir_temp,$virtual['file']);
                    file::write($filename, $virtual['contents']);
                    $this->files[] = $filename;
                }
            }
            // test file
            $testf = fs::ds(
                "{$this->dir_temp}/{$this->filename_temp}.php");
            file::write($testf,
                        $this->parsed['load'].$this->parsed['import'].
                        $this->parsed['test']);
            $this->files[] = $testf;

            // assemble command
            $command = implode(' ', [
                $php, $this->parsed['inip'], '-f "' . $testf . '"',
                $this->parsed['args'], '2>&1', $in]);

            // run the command
            // dump($this->parsed);
            $r = engine::run($command, $this->parsed['env'], $dir);
            if ($this->parsed['file_type'] === 'fileeof') {
                $r = preg_replace("/[\r\n]+$/", '', $r);
            }
            $r = trim($r);
            $this->output = $r;

            $c = $this->compare(
                $this->parsed['expect'],
                $this->output,
                ($this->parsed['expect_type'] === 'expectregex'));
            $this->status = $c ? 1 : 0;

            $et = microtime(true);
            $this->run_time = $et - $st;

            return $this->status;
        }
        /**
         * Get diff for this test.
         * @return array
         */
        function diff() {
            if (empty($this->diff) && ($this->status === 0)) {
                $this->diff = diff::generate(
                    $this->parsed['expect'],
                    $this->parsed['expect_raw'],
                    $this->output,
                    ($this->parsed['expect_type'] === 'expectregex'));
            }
            return $this->diff;
        }
        /**
         * Remove temp files.
         * @return null
         */
        function cleanup() {
            foreach ($this->files as $file) {
                file::remove($file);
            }
            $this->files = [];
        }
        /**
         * Was this test executed.
         * @return boolean
         */
        function executed() {
            return $this->status !== false;
        }
        /**
         * Was this test skipped.
         * @return boolean
         */
        function skipped() {
            return $this->status === -1;
        }
        /**
         * Message (why was it skipped)
         * @return string
         */
        function skipped_message() {
            return $this->skipif_message;
        }
        /**
         * Was this test successful.
         * @return boolean
         */
        function succeed() {
            return $this->status === 1;
        }
        /**
         * Weather this test failed.
         * @return boolean
         */
        function failed() {
            return $this->status === 0;
        }
        /**
         * Return test run time
         * @return float
         */
        function run_time() {
            return $this->run_time;
        }
        /**
         * Get filename.
         * @return string
         */
        function filename() {
            if (!$this->rel_filename) {
                $dir = [];
                $last = '';
                $dirname = $this->filename;

                do {
                    $dirname = dirname($dirname);
                    $last = basename($dirname);
                    array_unshift($dir, $last);
                } while ($last && $last !== 'tests');

                $file = file::name($this->filename);

                if (count($dir) > 0) {
                    $this->rel_filename = fs::ds(
                        implode('/', array_slice($dir, 1)), $file);
                } else {
                    $this->rel_filename = $file;
                }
            }
            return $this->rel_filename;
        }

        /**
         * Compare two strings to determine if they're the same.
         * @param  string  $expect
         * @param  string  $out
         * @param  boolean $regex
         * @return boolean
         */
        private function compare($expect, $out, $regex=false) {
            if ($regex) {
                return preg_match("/^{$expect}\$/s", $out);
            } else {
                return strcmp($out, $expect) === 0;
            }
        }
    }
}
