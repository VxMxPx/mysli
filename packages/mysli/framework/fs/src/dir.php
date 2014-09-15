<?php

namespace mysli\framework\fs {

    __use(__namespace__,
        ['../exception/*' => 'framework/exception/%s']
    );

    class dir {
        /**
         * Create a new directory.
         * @param  string  $directory
         * @param  integer $mode
         * @param  boolean $recursive
         * @return boolean
         */
        static function create($directory, $mode=0777, $recursive=true) {
            return mkdir($directory, $mode, $recursive);
        }
        /**
         * Copy directory and all the content to the destination.
         * If destination doesn't exists, it will be created.
         * @param  string  $source
         * @param  string  $destination
         * @param  boolean $recursive copy sub-directories
         * @param  boolean $overwrite if destination (file!) exists overwrite it
         * @return integer number of copied files and directories
         */
        static function copy($source, $destination, $recursive=true,
                             $overwrite=true) {
            $count = 0; // number of copied files and directories

            if (!self::exists($source)) {
                throw new framework\exception\argument(
                    "Not a valid directory: `{$source}`.", 1);
            }

            if (!self::exists($destination)) {
                if (!self::create($destination)) {
                    throw new framework\exception\fs(
                        "Cannot create destination directory: ".
                        "`{$destination}`.", 1);
                } else {
                    $count++;
                }
            }

            $files = array_diff(scandir($source), ['.', '..']);

            foreach ($files as $file) {
                $filename = fs::ds($source, $file);
                if (self::exists($filename)) {
                    if ($recursive) {
                        $count += self::copy(
                            $filename, fs::ds($destination, $file), $recursive,
                            $overwrite);
                    }
                } else {
                    try {
                        $count += file::copy(
                            $filename, fs::ds($destination, $file), $overwrite);
                    } catch (framework\exception\argument $e) {
                        // pass
                    }
                }
            }

            return $count;
        }
        /**
         * Move directory and all the content to the destination.
         * If destination doesn't exists, it will be created.
         * @param  string  $source
         * @param  string  $destination
         * @param  boolean $overwrite if destination (file!) exists overwrite it
         * @return integer number of moved files and directories
         */
        static function move($source, $destination, $overwrite=true) {
            $count = 0; // number of moved files and directories

            if (!self::exists($source)) {
                throw new framework\exception\argument(
                    "Not a valid directory: `{$source}`.", 1);
            }

            if (!self::exists($destination)) {
                if (!self::create($destination)) {
                    throw new framework\exception\fs(
                        "Cannot create destination directory: ".
                        "`{$destination}`.", 1);
                } else {
                    $count++;
                }
            }

            $files = array_diff(scandir($source), ['.', '..']);

            foreach ($files as $file) {
                $filename = fs::ds($source, $file);
                if (self::exists($filename)) {
                    $count += self::move(
                        $filename, fs::ds($destination, $file), $overwrite);
                } else {
                    try {
                        $count += file::move(
                            $filename, fs::ds($destination, $file), $overwrite);
                    } catch (framework\exception\argument $e) {
                        // pass
                    }
                }
            }

            try {
                self::remove($source, false);
            } catch (framework\exception\fs $e) {
                // pass
            }
            return $count;
        }
        /**
         * Removed a directory.
         * @param  string  $directory need to exists, cannot be empty string and
         * cannot be root '/'
         * @param  boolean $force remove non empty directory
         * @return boolean
         */
        static function remove($directory, $force=true) {
            if (!$directory || empty($directory) || trim($directory) === '/') {
                throw new framework\exception\argument(
                    'Argument $directory cannot be empty or /.', 1);
            }

            if (!self::exists($directory)) {
                throw new framework\exception\argument(
                    "Directory doesn't exists: `{$directory}`.", 2);
            }

            if (!self::is_empty($directory)) {
                if (!$force) {
                    throw new framework\exception\fs(
                        'Directory is not empty, use $force flag.', 1);
                }
                $files = array_diff(scandir($directory), ['.','..']);

                foreach ($files as $file) {
                    $filename = fs::ds($directory, $file);
                    if (self::exists($filename)) {
                        self::remove($filename, $force);
                        continue;
                    }
                    if (!unlink($filename)) {
                        throw new framework\exception\fs(
                            "Could not remove file: `{$filename}`.", 2);
                    }
                }
            }

            return rmdir($directory);
        }
        /**
         * Get signatures of all files in the directory +
         * sub directories if $deep is true.
         * @param  string  $directory
         * @param  boolean $deep
         * @param  boolean $ignore_hidden ignore hidden files and folders
         * @return array
         */
        static function signature($directory, $deep=true, $ignore_hidden=true) {

            if (!self::exists($directory)) {
                throw new framework\exception\argument(
                    "Invalid directory: `{$directory}`.", 1);
            }

            $result = [];
            $files = array_diff(scandir($directory), ['.','..']);

            foreach ($files as $file) {
                if ($ignore_hidden && substr($file, 0, 1) === '.') {
                    continue;
                }
                $filename = fs::ds($directory, $file);
                if (self::exists($filename)) {
                    $result = array_merge($result, self::signature($filename));
                } else {
                    $result[$filename] = file::signature($filename);
                }
            }

            return $result;
        }
        /**
         * Check weather directory is readable.
         * @param  string  $directory
         * @return boolean
         */
        static function is_readable($directory) {
            return is_readable($directory);
        }
        /**
         * Check if there are files in the directory.
         * @param  string $directory
         * @return boolean
         */
        static function is_empty($directory) {
            if (!self::is_readable($directory)) {
                throw new framework\exception\fs(
                    'The directory is not readable!', 1);
            }

            $handle = opendir($directory);
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    return false;
                }
            }

            return true;
        }
        /**
         * Return directory size in bytes.
         * @param  string  $directory
         * @param  boolean $deep weather to include sub-directories
         * @return integer
         */
        static function size($directory, $deep=true) {
            if (!self::exists($directory)) {
                throw new framework\exception\not_found(
                    "Directory not found: `{$directory}`.", 1);
            }
            $files = array_diff(scandir($directory), ['.','..']);
            $size = 0;

            foreach ($files as $file) {
                if (self::exists(fs::ds($directory, $file))) {
                    if ($deep) {
                        $size += self::size(fs::ds($directory, $file));
                    }
                } else {
                    $size += file::size(fs::ds($directory, $file));
                }
            }

            return $size;
        }
        /**
         * Check if directory exists.
         * @param  string $directory
         * @return boolean
         */
        static function exists($directory) {
            return file_exists($directory) && is_dir($directory);
        }
    }
}
