<?php

namespace Mysli\Core\Lib;

class FS
{
    const EXISTS_REPLACE = 'replace';
    const EXISTS_MERGE   = 'merge';
    const EXISTS_RENAME  = 'rename';
    const EXISTS_ERROR   = 'error';
    const EXISTS_IGNORE  = 'ignore';

    /**
     * Convert size (from bytes) to nicer (human readable) value (kb, mb)
     * Return: bytes|KB|MB|GB
     * --
     * @param   integer $size (bytes)
     * @param   integer $round_precision
     * --
     * @return  array   [12, 'MB']
     */
    public static function format_size($size, $round_precision = 4)
    {
        $size = floatval($size);

        if ($size < 1024) {
            return [$size, 'bytes'];
        }
        elseif ($size < 1048576) {
            return [round($size/1024, $round_precision), 'KB'];
        }
        else {
            $result = round($size/1048576, $round_precision);
            if ($result > 1024) {
                $result = [round($result/1024, $round_precision), 'GB'];
            } else {
                $result = [$result, 'MB'];
            }
            return $result;
        }
    }

    /**
     * Rename file or directory.
     * --
     * @param  mixed $old String or array [old => new, old => new]
     * @param  mixed $new String or null (if first param is array)
     * --
     * @return integer    Number of renamed files.
     */
    public static function rename($old, $new=null)
    {
        if (is_array($old)) {
            $renamed = 0;
            foreach ($old as $o => $n) {
                $renamed = $renamed + self::rename($o, $n);
            }
            return $renamed;
        }

        if (rename($old, $new)) {
            return 1;
        }
        else {
            trigger_error(
                "Error while renaming file: `{$old}`, to `{$new}`.",
                E_USER_WARNING
            );
            return 0;
        }
    }

    /**
     * Will generate unique prefix for particular file / folder.
     * --
     * @param  string $filename      Full path
     * --
     * @return string New filename
     */
    public static function unique_prefix($filename) {
        $destination = dirname($filename);
        $filename    = basename($filename);
        return md5(ds($destination, $filename)) . '_' . $filename;
    }

    /**
     * Return only extension of file, if available, otherwise an empty string.
     * --
     * @param  string $filename
     * --
     * @return string
     */
    public static function file_extension($filename)
    {
        $file = basename($filename);
        if (strpos($file, '.') === false) {
            return '';
        }
        $extension = explode('.', strrev($file), 2);
        return strrev($extension[0]);
    }

    /**
     * Get filename.
     * --
     * @param  string  $filename
     * @param  boolean $extension
     * --
     * @return string
     */
    public static function file_get_name($filename, $extension = false)
    {
        $filename = basename($filename);
        if (!$extension) {
            $file_ext = self::file_extension($filename);
            $file_ext = strlen($file_ext);
            if ($file_ext > 0) {
                return substr($filename, 0, ($file_ext + 1) * -1);
            }
        }
        return $filename;
    }

    /**
     * Will generate new unique file / dir name, if the file/dir already exists.
     * --
     * @param  string $filename    Full path.
     * @param  string $divider     E.g. file.txt => file_2.txt when divider is _
     * --
     * @return string              /absolute/path/to/file
     */
    public static function unique_name($filename, $divider = '_')
    {
        $directory    = dirname($filename);
        $filename     = basename($filename);
        $new_filename = $filename;
        $ext          = self::file_extension($filename);
        $ext          = empty($ext) ? '' : '.' . $ext;
        $base         = self::file_get_name($filename, false);
        $n            = 2;

        while (file_exists(ds($directory, $new_filename))) {
            $new_filename = $base . $divider . $n . $ext;
            $n++;
        }

        return ds($directory, $new_filename);
    }

    /**
     * Will get file's content if file exists.
     * --
     * @param  string $filename
     * --
     * @return string or false if file doesn't exists.
     */
    public static function file_read($filename)
    {
        if (file_exists($filename)) {
            return file_get_contents($filename);
        } else {
            trigger_error("File not found: `{$filename}`.", E_USER_WARNING);
            return false;
        }
    }

    /**
     * Will create new file, if doesn't exists already.
     * If it does exists (and $empty = true) it will remove existing content.
     * --
     * @param  string  $filename
     * @param  boolean $empty
     * --
     * @throws FSException If file couldn't be created.
     * --
     * @return boolean
     */
    public static function file_create($filename, $empty = false)
    {
        if (file_exists($filename)) {
            if (!$empty) { return false; }
            if (file_put_contents($filename, '') === false) {
                trigger_error(
                    "Couldn't remove file's contents: `{$filename}`.",
                    E_USER_WARNING
                );
                return false;
            }
        }

        if (touch($filename)) {
            return true;
        } else {
            throw new \Mysli\Core\FSException(
                "Could not create file file: `{$filename}`."
            );
        }
    }

    /**
     * If file exists this will append content to it. If it doesn't exists,
     * it will create it (when $create = true).
     * --
     * @param  string  $filename
     * @param  string  $content
     * @param  boolean $create   Should file be created if doesn't exists.
     * --
     * @throws FSException If can't write to file.
     * --
     * @return integer Number of bytes written, or FALSE on failure.
     */
    public static function file_append($filename, $content, $create = true)
    {
        if ($create) {
            // This works only if file doesn't exists.
            self::file_create($filename);
        }

        if (file_exists($filename)) {
            return file_put_contents($filename, $content, FILE_APPEND);
        } else {
            throw new \Mysli\Core\FSException(
                "Could not write to file: `{$filename}`."
            );
        }
    }

    /**
     * Will prepend content to the file!
     * --
     * @param  string  $filename
     * @param  string  $content
     * @param  boolean $create
     * --
     * @throws FSException If can't write to file.
     * --
     * @return mixed   integer or when error boolean false
     */
    public static function file_prepend($filename, $content, $create = true)
    {
        if ($create) {
            // This works only if file doesn't exists.
            self::file_create($filename);
        }

        if (file_exists($filename)) {
            $handle = fopen($filename, 'r+');
            $content_length = strlen($content);
            $sum_length = filesize($filename) + $content_length;
            $content_old = fread($handle, $content_length);
            rewind($handle);
            $i = 1;
            while (ftell($handle) < $sum_length) {
                fwrite($handle, $content);
                $content = $content_old;
                $content_old = fread($handle, $content_length);
                fseek($handle, $i * $content_length);
                $i++;
            }
            return $i;
        } else {
            throw new \Mysli\Core\FSException(
                "Could not write to file: `{$filename}`."
            );
        }
    }

    /**
     * Will replace file content.
     * --
     * @param  string  $filename
     * @param  string  $content
     * @param  boolean $create
     * --
     * @throws FSException If can't write to file.
     * --
     * @return mixed   integer or when error boolean false
     */
    public static function file_replace($filename, $content, $create = true)
    {
        if ($create) {
            // This works only if file doesn't exists.
            self::file_create($filename);
        }

        if (file_exists($filename)) {
            return file_put_contents($filename, $content);
        } else {
            throw new \Mysli\Core\FSException(
                "Could not write to file: `{$filename}`."
            );
        }
    }

    /**
     * Empty the file -- erase all content.
     * --
     * @param  string  $filename
     * @param  boolean $create
     * --
     * @return boolean
     */
    public static function file_empty($filename, $create = true)
    {
        return self::file_replace($filename, '', $create) !== false;
    }

    /**
     * Remove one (or more files).
     * --
     * @param  mixed $file String or array (when want to remove more files).
     * --
     * @return mixed Integer (number of removed files) or false on error.
     */
    public static function file_remove($file)
    {
        if (is_array($file)) {
            $i = 0;
            foreach ($file as $f) {
                $i = $i + self::file_remove($f);
            }
            return $i;
        }

        return unlink($file) ? 1 : 0;
    }

    /**
     * Copy one of more files.
     * Examples:
     *     $source = '/home/me/my_file.txt',
     *     $destination = '/home/me/doc'
     *
     *     $source = '/home/me/my_file.txt',
     *     $destination = '/home/me/doc/new_filename.txt'
     *
     *     $source = ['/home/me/my_file.txt', '/home/me/my_file_2.txt'],
     *     $destination = '/home/me/doc'
     *
     *     $source = [
     *         '/home/me/my_file.txt' => '/home/me/doc/file_1.txt',
     *         '/home/me/my_file_2.txt' => '/home/me/doc/file_2_new.txt'
     *     ],
     *     $destination = null
     * --
     * @param  mixed   $source       String or Array
     * @param  string  $destination
     * @param  boolean $on_exists
     * --
     * @throws ValueException If Source file doesn't exists (10)
     * @throws ValueException If Source is directory (11)
     * @throws ValueException If Destination isn't directory (12)
     * @throws ValueException If Invalid value for $on_exists (20)
     * @throws FSException If Destination (file) exists and $on_exists is self::EXISTS_ERROR (10)
     * @throws FSException If Can't copy file (copy function failed) (11)
     * --
     * @return integer Number of copied files.
     */
    public static function file_copy(
        $source,
        $destination = null,
        $on_exists = self::EXISTS_REPLACE
    ) {
        if (is_array($source)) {
            $i = 0;
            foreach ($source as $k => $v) {
                if (!is_numeric($k)) {
                    $i = $i + self::file_copy($k, $v, $on_exists);
                } else {
                    $i = $i + self::file_copy($v, $destination, $on_exists);
                }
            }
            return $i;
        }

        if (!file_exists($source)) {
            throw new \Mysli\Core\ValueException(
                "Source file doesn't exists: `{$source}`.",
                10
            );
        }

        if (is_dir($source)) {
            throw new \Mysli\Core\ValueException(
                "Cannot copy directory: `{$source}`, use `dir_copy` method!",
                11
            );
        }

        if (!is_dir($destination) && !is_dir(dirname($destination))) {
            throw new \Mysli\Core\ValueException(
                "Destination isn't directory: `{$destination}`.",
                12
            );
        }

        $source_file = basename($source);
        $destination = is_dir($destination)
                            ? ds($destination, $source_file)
                            : $destination;

        if (file_exists($destination)) {
            switch ($on_exists) {
                case self::EXISTS_IGNORE:
                    // with(core('log'))->info(
                    //     "File exists: `{$destination}`. Ignoring.",
                    //     __FILE__, __LINE__
                    // );
                    return 0;
                    break;

                case self::EXISTS_ERROR:
                    throw new \Mysli\Core\FSException(
                        "File exists: `{$destination}`.",
                        10
                    );
                    break;

                case self::EXISTS_REPLACE:
                    // with(core('log'))->info(
                    //     "File exists: `{$destination}`. It will be replaced.",
                    //     __FILE__, __LINE__
                    // );
                    break;

                case self::EXISTS_RENAME:
                    // with(core('log'))->info(
                    //     "File exists: `{$destination}`. It will be renamed.",
                    //     __FILE__, __LINE__
                    // );
                    $destination = self::unique_name($destination);
                    break;

                default:
                    throw new \Mysli\Core\ValueException(
                        "Invalid value for \$on_exists: `{$on_exists}`.",
                        20
                    );
            }
        }

        if (copy($source, $destination)) {
            // with(core('log'))->info(
            //     "File was copied: `{$source}`, to: `{$destination}`.",
            //     __FILE__, __LINE__
            // );
            return 1;
        }
        else {
            throw new \Mysli\Core\FSException(
                "Error, can't copy file: `{$source}`, to: `{$destination}`.",
                11
            );
        }
    }

    /**
     * Move one of more files.
     * Examples:
     *     $source = '/home/me/my_file.txt',
     *     $destination = '/home/me/doc'
     *
     *     $source = '/home/me/my_file.txt',
     *     $destination = '/home/me/doc/new_filename.txt'
     *
     *     $source = ['/home/me/my_file.txt', '/home/me/my_file_2.txt'],
     *     $destination = '/home/me/doc'
     *
     *     $source = [
     *         '/home/me/my_file.txt' => '/home/me/doc/file_1.txt',
     *         '/home/me/my_file_2.txt' => '/home/me/doc/file_2_new.txt'
     *     ],
     *     $destination = null
     * --
     * @param  mixed   $source       String or Array
     * @param  string  $destination
     * @param  boolean $on_exists
     * --
     * @throws ValueException If Source file doesn't exists (10)
     * @throws ValueException If Source is directory (11)
     * @throws ValueException If Destination isn't directory (12)
     * @throws ValueException If Invalid value for $on_exists (20)
     * @throws FSException If Destination (file) exists and $on_exists is self::EXISTS_ERROR (10)
     * @throws FSException If Can't move file (rename function failed) (11)
     * --
     * @return mixed Integer (number of copied files) or boolean false on error.
     */
    public static function file_move(
        $source,
        $destination = null,
        $on_exists   = self::EXISTS_REPLACE
    ) {
        if (is_array($source)) {
            $i = 0;
            foreach ($source as $k => $v) {
                if (!is_numeric($k)) {
                    $i = $i + self::file_move($k, $v, $on_exists);
                } else {
                    $i = $i + self::file_move($v, $destination, $on_exists);
                }
            }
            return $i;
        }

        if (!file_exists($source)) {
            throw new \Mysli\Core\ValueException(
                "Source file doesn't exists: `{$source}`.",
                10
            );
        }

        if (is_dir($source)) {
            throw new \Mysli\Core\ValueException(
                "Cannot move directory: `{$source}`, use `dir_move` method!",
                11
            );
        }

        if (!is_dir($destination) && !is_dir(dirname($destination))) {
            throw new \Mysli\Core\ValueException(
                "Destination isn't directory: `{$destination}`.",
                12
            );
        }

        $source_file = basename($source);
        $destination = is_dir($destination)
                            ? ds($destination, $source_file)
                            : $destination;

        if (file_exists($destination)) {
            switch ($on_exists) {
                case self::EXISTS_IGNORE:
                    // with(core('log'))->info(
                    //     "File exists: `{$destination}`. Ignoring.",
                    //     __FILE__, __LINE__
                    // );
                    return 0;
                    break;

                case self::EXISTS_ERROR:
                    throw new \Mysli\Core\FSException(
                        "File exists: `{$destination}`.",
                        10
                    );
                    break;

                case self::EXISTS_REPLACE:
                    // with(core('log'))->info(
                    //     "File exists: `{$destination}`. It will be replaced.",
                    //     __FILE__, __LINE__
                    // );
                    break;

                case self::EXISTS_RENAME:
                    // with(core('log'))->info(
                    //     "File exists: `{$destination}`. It will be renamed.",
                    //     __FILE__, __LINE__
                    // );
                    $destination = self::unique_name($destination);
                    break;

                default:
                    throw new \Mysli\Core\ValueException(
                        "Invalid value for \$on_exists: `{$on_exists}`.",
                        20
                    );
            }
        }

        if (rename($source, $destination)) {
            // with(core('log'))->info(
            //     "File was renamed: `{$source}`, to: `{$destination}`.",
            //     __FILE__, __LINE__
            // );
            return 1;
        }
        else {
            throw new \Mysli\Core\FSException(
                "Error, can't rename file: `{$source}`, to: `{$destination}`.",
                11
            );
        }
    }

    /**
     * Will search for files in particular directory.
     * --
     * @param  string  $directory
     * @param  string  $filter_regex Regular expression filter, e.g. /*.\.jpg/i
     * @param  boolean $deep         Will also search in sub-directories.
     * --
     * @throws ValueException If $directory is not valid directory.
     * --
     * @return array
     */
    public static function file_search($directory, $filter_regex, $deep = true)
    {
        if (!is_dir($directory)) {
            throw new \Mysli\Core\ValueException(
                "Can't find files, not a valid directory: `{$directory}`.", 30
            );
        }

        $collection = array_diff(scandir($directory), ['.','..']);
        $matched    = [];

        if (!is_array($collection) || empty($collection)) {
            return [];
        }

        foreach ($collection as $file) {
            // We have directory, should we scan it?
            if (is_dir(ds($directory, $file))) {
                if (!$deep) continue;
                $matched_sub = self::file_search(ds($directory, $file), $filter_regex, $deep);
                return array_merge($matched_sub, $matched);
            }
            if (preg_match($filter_regex, $file)) {
                $matched[] = ds($directory, $file);
            }
        }

        return $matched;
    }

    /**
     * Will return md5 signature of specified file(s).
     * --
     * @param  mixed $filename Filename (full path), or an array, collection of
     *                         files, e.g. ['/abs/path/file.1', '/abs/path/file.2']
     * --
     * @return mixed           String or array, depends on input!
     *                         null if file not found
     */
    public static function file_signature($filename)
    {
        if (is_array($filename)) {
            $collection = [];
            foreach ($filename as $file) {
                $collection[] = self::file_signature($file);
            }
            return $collection;
        }

        if (!file_exists($filename)) {
            trigger_error("File not found: `{$filename}`.", E_USER_WARNING);
            return null;
        }

        return md5_file($filename);
    }

    /**
     * Check if particular file is is stored in publicly accessible directory,
     * and hence is (possibly) accessible through URL.
     * --
     * @param  string $filename
     * --
     * @return boolean
     */
    // public static function is_public($filename)
    // {
    //     $public_length = strlen(pubpath());
    //     if (ds(substr($filename, 0, $public_length)) !== pubpath()) {
    //         return false;
    //     } else {
    //         return true;
    //     }
    // }

    /**
     * Return file URI from absolute path on the server. Works only if the file
     * is publicly accessible.
     * --
     * @param  string $filename
     * --
     * @return string Full url, or empty if not public
     */
    // public static function get_uri($filename)
    // {
    //     if (!self::is_public($filename)) {
    //         return '';
    //     }

    //     $filename = substr($filename, strlen(pubpath()));
    //     return str_replace('\\', '/', $filename);
    // }

    /**
     * Return file URL from absolute path on the server. Works only if file is
     * publicly accessible.
     * --
     * @param  string $filename
     * --
     * @return string
     */
    // public static function get_url($filename)
    // {
    //     return with(core('server'))->url(self::get_uri($filename));
    // }

    // Directories Methods -----------------------------------------------------

    /**
     * Get signatures of all files in the directory +
     * sub directories if $deep is true.
     * --
     * @param  string  $directory
     * @param  boolean $deep
     * --
     * @return array
     */
    public static function dir_signatures($directory, $deep = true)
    {
        $result = [];
        $files = array_diff(scandir($directory), ['.','..']);
        foreach ($files as $file) {
            $filename = ds($directory, $file);
            if (is_dir($filename)) {
                $result = array_merge($result, self::dir_signatures($filename));
            } else {
                $result[$filename] = self::file_signature($filename);
            }
        }
        return $result;
    }

    /**
     * Check if there are some files in the directory.
     * --
     * @param  string $directory
     * --
     * @throws FSException If directory ($directory) is not readable.
     * --
     * @return boolean
     */
    public static function dir_is_empty($directory)
    {
        if (!is_readable($directory)) {
            throw new \Mysli\Core\FSException(
                'The directory is not readable!', 30
            );
        }

        $handle = opendir($directory);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                return false;
            }
        }

        return true;
    }

    /**
     * Removed directory.
     * If force is true, then it will removed non empty directories also.
     * Note: directory need to exists,
     * string cannot be empty and cannot be '/'.
     * --
     * @param  string  $directory
     * @param  boolean $force
     * --
     * @throws ValueException If Director is an empty string or / (40)
     * @throws ValueException If Directory doesn't exists (41)
     * @throws ValueException If Directory is not empty and $force if false (42)
     * @throws FSException    If one of the files could not be removed. (40)
     * --
     * @return boolean
     */
    public static function dir_remove($directory, $force = true)
    {
        if (!$directory || empty($directory) || trim($directory) === '/') {
            throw new \Mysli\Core\ValueException(
                'Directory cannot be empty or /.', 40
            );
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            throw new \Mysli\Core\ValueException(
                'Directory doesn\'t exists: ' . $directory, 41
            );
        }

        if (!self::dir_is_empty($directory)) {
            if (!$force) {
                throw new \Mysli\Core\ValueException(
                    'Directory is not empty, please use $force flag.', 42
                );
            }
            $files = array_diff(scandir($directory), ['.','..']);
            foreach ($files as $file) {
                $filename = ds($directory, $file);
                if (is_dir($filename)) {
                    self::dir_remove($filename, true);
                    continue;
                }
                if (!unlink($filename)) {
                    throw new \Mysli\Core\FSException(
                        "Could not remove file: `{$filename}`.", 40
                    );
                }
            }
        }

        return rmdir($directory);
    }

    /**
     * Will copy directory (plus all the content to the destination).
     * The destination directory will be created.
     * --
     * @param  string  $source Options:
     * @param  string  $destination
     * @param  integer $on_exists
     *     EXISTS_REPLACE // Delete destination if exists
     *     EXISTS_MERGE   // Merge source + destination
     *                       OR throw FSException if destination is a file.
     *     EXISTS_RENAME  // Rename (new) destination
     *     EXISTS_ERROR   // Throw FSException
     *     EXISTS_IGNORE  // Skip quietly, return null
     * --
     * @throws ValueException If Source doesn't exists (1)
     * @throws ValueException If Source is not a valid directory. (2)
     * @throws ValueException If Invalid value for $on_exists (3)
     * @throws FSException If Cannot delete destination (when self::EXISTS_REPLACE) (1)
     * @throws FSException If Destination is file (when self::EXISTS_MERGE) (2)
     * @throws FSException If Destination exists (when self::EXISTS_ERROR) (3)
     * @throws FSException If Couldn't create directory (mkdir failed) (4)
     * --
     * @return mixed      True || Null: ignore || Integer: number of skipped files.
     */
    public static function dir_copy(
        $source,
        $destination,
        $on_exists = self::EXISTS_MERGE
    ) {
        if (!file_exists($source)) {
            throw new \Mysli\Core\ValueException(
                "Source doesn't exists: `{$source}`.", 1
            );
        }

        if (!is_dir($source)) {
            throw new \Mysli\Core\ValueException(
                "Not a valid directory: `{$source}`.", 2
            );
        }

        if (file_exists($destination)) {
            switch ($on_exists) {
                case self::EXISTS_REPLACE:
                    if (!is_dir($destination)) {
                        unlink($destination);
                    } else {
                        self::dir_remove($destination, true);
                    }
                    if (file_exists($destination)) {
                        throw new \Mysli\Core\FSException(
                            "Cannot delete destination: `{$destination}`", 1
                        );
                    }
                    break;

                case self::EXISTS_MERGE:
                    if (!is_dir($destination)) {
                        throw new \Mysli\Core\FSException(
                            "Destination is file, so cannot merge: `{$destination}`", 2
                        );
                    }
                    // Continue...
                    break;

                case self::EXISTS_RENAME:
                    $destination = self::unique_name($destination);
                    break;

                case self::EXISTS_ERROR:
                    throw new \Mysli\Core\FSException(
                        "Directory exists: `{$destination}`.", 3
                    );

                case self::EXISTS_IGNORE:
                    return null;

                default:
                    throw new \Mysli\Core\ValueException(
                        "Invalid value for \$on_exists: `{$on_exists}`.", 3
                    );
            }
        }

        if (!file_exists($destination)) {
            if (!mkdir($destination, 0777, true)) {
                throw new \Mysli\Core\FSException(
                    "Couldn't create the directory: `{$destination}`.", 4
                );
            }
        }

        $files = array_diff(scandir($source), ['.', '..']);
        $skipped = 0; // Number of skipped files

        foreach ($files as $file) {
            $filename = ds($source, $file);
            if (is_dir($filename)) {
                $count = self::dir_copy(
                    $filename,
                    ds($destination, $file),
                    $on_exists
                );
                $skipped += $count === true ? 0 : $count;
            } else {
                if (!copy($filename, ds($destination, $file))) {
                    trigger_error(
                        "Couldn't copy, will skip: `{$filename}`.",
                        E_USER_WARNING
                    );
                    $skipped++;
                }
            }
        }

        return $skipped > 0 ? $skipped : true;
    }

    /**
     * Will move directory (plus all the content to the destination).
     * The destination directory will be created.
     * --
     * @param  string  $source
     * @param  string  $destination
     * @param  integer $on_exists
     *     EXISTS_REPLACE // Delete destination if exists
     *     EXISTS_MERGE   // Merge source + destination
     *                       OR throw FSException if destination is a file.
     *     EXISTS_RENAME  // Rename (new) destination
     *     EXISTS_ERROR   // Throw FSException
     *     EXISTS_IGNORE  // Skip quietly, return null
     * --
     * @throws ValueException If Source doesn't exists (1)
     * @throws ValueException If Source is not a valid directory. (2)
     * @throws ValueException If Invalid value for $on_exists (3)
     * @throws FSException If Cannot delete destination (when self::EXISTS_REPLACE) (1)
     * @throws FSException If Destination is file (when self::EXISTS_MERGE) (2)
     * @throws FSException If Destination exists (when self::EXISTS_ERROR) (3)
     * @throws FSException If Couldn't create directory (mkdir failed) (4)
     * --
     * @return boolean
     */
    public static function dir_move(
        $source,
        $destination,
        $on_exists = self::EXISTS_MERGE
    ) {
        if (self::dir_copy($source, $destination, $on_exists) === true) {
            self::dir_remove($source, true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create new directory.
     * --
     * @param  string  $name
     * @param  integer $on_exists
     *     EXISTS_REPLACE // Delete destination if exists
     *     EXISTS_MERGE   // Return true if DIR,
     *                       OR throw FSException if file.
     *     EXISTS_RENAME  // Rename the (new) destination
     *     EXISTS_ERROR   // Throw FSException
     *     EXISTS_IGNORE  // Skip quietly, return null
     * @param integer $mode       The mode is 0777 by default, which means
     *                            the widest possible access.
     * @param boolean $recursive  Allows the creation of nested directories
     *                            specified in the pathname.
     * --
     * @throws FSException If directory could not be created.
     * @throws FSException If destination exists and $on_exists set to:
     *                             EXISTS_MERGE (and destination is file) or
     *                             EXISTS_ERROR
     * @throws ValueException      If Invalid $on_exists value is set.
     * --
     * @return mixed  string on success (full path), false on failure
     */
    public static function dir_create(
        $destination,
        $on_exists = self::EXISTS_IGNORE,
        $mode = 0777,
        $recursive = false
    ) {
        if (file_exists($destination)) {
            switch ($on_exists) {
                case self::EXISTS_REPLACE:
                    self::dir_remove($destination, true);
                    break;

                case self::EXISTS_MERGE:
                    if (!is_dir($destination)) {
                        throw new \Mysli\Core\FSException(
                            "Destination is file, so cannot merge: `{$destination}`",
                            1
                        );
                    }
                    return $destination;

                case self::EXISTS_RENAME:
                    $destination = self::unique_name($destination);
                    break;

                case self::EXISTS_ERROR:
                    throw new \Mysli\Core\FSException(
                        "Directory exists: `{$destination}`.", 2
                    );

                case self::EXISTS_IGNORE:
                    return null;

                default:
                    throw new \Mysli\Core\ValueException(
                        "Invalid value for \$on_exists: `{$on_exists}`.", 3
                    );
            }
        }

        if (!mkdir($destination, $mode, $recursive)) {
            throw new \Mysli\Core\FSException(
                "Filed to create directory: `{$destination}`", 4
            );
        } else return $destination;
    }
}