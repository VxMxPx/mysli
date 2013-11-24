<?php

namespace Mysli\Core\Lib;

class FS
{
    const EXISTS_REPLACE = 'replace';
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
            Log::info(
                "File was renamed from: `{$old}`, to `{$new}`.",
                __FILE__, __LINE__
            );
            return 1;
        }
        else {
            Log::warn(
                "Error while renaming file: `{$old}`, to `{$new}`.",
                __FILE__, __LINE__
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
     * Will generate new unique filename, if the file already exists.
     * --
     * @param  string $filename    Full path.
     * @param  string $divider     E.g. file.txt => file_2.txt when divider is _
     * --
     * @return string              New filename + full path.
     */
    public static function file_unique_name($filename, $divider='_')
    {
        $destination  = dirname($filename);
        $filename     = basename($filename);
        $new_filename = $filename;

        if (file_exists(ds($destination, $filename)) &&
            !is_dir(ds($destination, $filename))
        ) {
            $ext  = self::file_extension($filename);
            $base = self::file_get_name($filename, false);
            $n    = 2;
            do {
                $new_filename =
                    $base .
                    $divider .
                    $n .
                    (empty($ext) ? '' : '.' . $ext);
                $n++;
            }
            while(file_exists(ds($destination, $new_filename)) &&
                !is_dir(ds($destination, $new_filename)));
            Log::info(
                "Generated unique filename: `{$new_filename}`.",
                __FILE__, __LINE__
            );
        }

        return ds($destination, $new_filename);
    }

    /**
     * Will get file's content if file exists.
     * --
     * @param  string  $filename
     * --
     * @return string  or false if file doesn't exists.
     */
    public static function file_read($filename)
    {
        if (file_exists($filename)) {
            return file_get_contents($filename);
        } else {
            Log::warn("File not found: `{$filename}`.", __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * Will create new file, if doesn't exists already.
     * If it does exists (and $empty=true) it will remove existing content.
     * --
     * @param  string  $filename
     * @param  boolean $empty
     * --
     * @return boolean
     */
    public static function file_create($filename, $empty = false)
    {
        if (file_exists($filename)) {
            if (!$empty) { return false; }
            if (file_put_contents($filename, '') === false) {
                Log::warn(
                    "Couldn't remove file's contents: `{$filename}`.",
                    __FILE__, __LINE__
                );
                return false;
            }
        }

        if (touch($filename)) {
            return true;
        } else {
            Log::error(
                "Could not touch file: `{$filename}`.",
                __FILE__, __LINE__
            );
            return false;
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
            Log::error("Could not write to file: `{$filename}`.", __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * Will prepend content to the file!
     * --
     * @param  string  $filename
     * @param  string  $content
     * @param  boolean $create
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
            Log::error(
                "Could not write to file: `{$filename}`.",
                __FILE__, __LINE__
            );
            return false;
        }
    }

    /**
     * Will replace file content.
     * --
     * @param  string  $filename
     * @param  string  $content
     * @param  boolean $create
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
            Log::error(
                "Could not write to file: `{$filename}`.",
                __FILE__, __LINE__
            );
            return false;
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
     * @return mixed   Integer (number of copied files) or boolean false on error.
     */
    public static function file_copy(
        $source,
        $destination = null,
        $on_exists = FS::REPLACE
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
            Log::warn(
                "Source file doesn't exists: `{$source}`.",
                __FILE__, __LINE__
            );
            return false;
        }

        if (is_dir($source)) {
            Log::warn(
                "Cannot copy directory: `{$source}`, user `dir_copy` method!",
                __FILE__, __LINE__
            );
            return false;
        }

        if (!is_dir($destination) && !is_dir(dirname($destination))) {
            Log::warn(
                "Destination isn't directory: `{$destination}`.",
                __FILE__, __LINE__
            );
            return false;
        }

        $source_file = basename($source);
        $destination = is_dir($destination)
                            ? ds($destination, $source_file)
                            : $destination;

        if (file_exists($destination)) {
            switch ($on_exists) {
                case self::EXISTS_IGNORE:
                    Log::info(
                        "File exists: `{$destination}`. Ignoring.",
                        __FILE__, __LINE__
                    );
                    return 0;
                    break;

                case self::EXISTS_ERROR:
                    trigger_error(
                        "File exists: `{$destination}`.",
                        E_USER_ERROR
                    );
                    break;

                case self::EXISTS_REPLACE:
                    Log::info(
                        "File exists: `{$destination}`. It will be replaced.",
                        __FILE__, __LINE__
                    );
                    break;

                case self::EXISTS_RENAME:
                    Log::info(
                        "File exists: `{$destination}`. It will be renamed.",
                        __FILE__, __LINE__
                    );
                    $destination = self::file_unique_name($destination);
                    break;

                default:
                    trigger_error(
                        "Invalid value for \$on_exists: `{$on_exists}`."
                    );
            }
        }

        if (copy($source, $destination)) {
            Log::info(
                "File was copied: `{$source}`, to: `{$destination}`.",
                __FILE__, __LINE__
            );
            return 1;
        }
        else {
            Log::error(
                "Error, can't copy file: `{$source}`, to: `{$destination}`.",
                __FILE__, __LINE__
            );
            return false;
        }
    }

    /**
     * Move one of more files.
     * Examples:
     * $source = '/home/me/my_file.txt', $destination = '/home/me/documents'
     * $source = '/home/me/my_file.txt', $destination = '/home/me/documents/different_filename.txt'
     * $source = ['/home/me/my_file.txt', '/home/me/my_file_2.txt'], $destination = '/home/me/documents'
     * $source = ['/home/me/my_file.txt' => '/home/me/documents/file_1.txt',
     *            '/home/me/my_file_2.txt' => '/home/me/documents/file_2_new.txt'],
     *            $destination = null
     * --
     * @param  mixed   $source       String or Array
     * @param  string  $destination
     * @param  boolean $on_exists
     * --
     * @return mixed   Integer (number of copied files) or boolean false on error.
     */
    public static function file_move($source, $destination, $on_exists=FS::REPLACE)
    {
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
            Log::warn("Source file doesn't exists: `{$source}`.", __FILE__, __LINE__);
            return false;
        }

        if (is_dir($source)) {
            Log::warn("Cannot move directory: `{$source}`, user `dir_move` method!", __FILE__, __LINE__);
            return false;
        }

        if (!is_dir($destination) && !is_dir(dirname($destination))) {
            Log::warn("Destination isn't directory: `{$destination}`.", __FILE__, __LINE__);
            return false;
        }

        $source_file = basename($source);
        $destination = is_dir($destination)
                            ? ds($destination, $source_file)
                            : $destination;

        if (file_exists($destination)) {
            switch ($on_exists) {
                case self::EXISTS_IGNORE:
                    Log::info("File exists: `{$destination}`. Ignoring.", __FILE__, __LINE__);
                    return 0;
                    break;

                case self::EXISTS_ERROR:
                    trigger_error("File exists: `{$destination}`.", E_USER_ERROR);
                    break;

                case self::EXISTS_REPLACE:
                    Log::info("File exists: `{$destination}`. It will be replaced.", __FILE__, __LINE__);
                    break;

                case self::EXISTS_RENAME:
                    Log::info("File exists: `{$destination}`. It will be renamed.", __FILE__, __LINE__);
                    $destination = self::file_unique_name($destination);
                    break;

                default:
                    trigger_error("Invalid value for \$on_exists: `{$on_exists}`.");
            }
        }

        if (rename($source, $destination)) {
            Log::info("File was renamed: `{$source}`, to: `{$destination}`.", __FILE__, __LINE__);
            return 1;
        }
        else {
            Log::error("Error, can't rename file: `{$source}`, to: `{$destination}`.", __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * Will search for files in particular directory.
     * --
     * @param  string  $directory
     * @param  string  $filter_regex Regular expression filter, e.g. /*.\.jpg/i
     * @param  boolean $deep         Will also search in sub-directories.
     * --
     * @return array
     */
    public static function file_search($directory, $filter_regex, $deep=true)
    {
        if (!is_dir($directory)) {
            Log::warn("Can't find files, not a valid directory: `{$directory}`.");
            return [];
        }

        $collection = scandir($directory);
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
            Log::warn("File not found: `{$filename}`.", __FILE__, __LINE__);
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
    public static function file_is_public($filename)
    {
        $public_length = strlen(pubpath());
        if (ds(substr($filename, 0, $public_length)) !== pubpath()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return file URI from absolute path on the server. Works only if the file
     * is publicly accessible.
     * --
     * @param  string $filename
     * --
     * @return string
     */
    public static function file_get_uri($filename)
    {
        if (!file_is_public($filename)) {
            return '';
        }

        $filename = substr($filename, strlen(pubpath()));
        return str_replace('\\', '/', $filename);
    }

    /**
     * Return file URL from absolute path on the server. Works only if file is
     * publicly accessible.
     * --
     * @param  string $filename
     * --
     * @return string
     */
    public static function file_get_url($filename)
    {
        return Server::url(self::file_get_uri($filename));
    }

    /**
     * Will generate new unique directory name, if the directory already exists.
     * --
     * @param  string $dirname  Full path.
     * @param  string $divider  E.g. /files => /files_2 when divider is _
     * --
     * @return string           New dirname + full path.
     */
    public static function dir_unique_name($dirname, $divider='_')
    {
        $destination = dirname($filename);
        $dirname     = filename($dirname);

        if (file_exists(ds($destination, $dirname)) && is_dir(ds($destination, $dirname))) {
            $n = 2;
            do {
                $new_dirname = $base . $divider . $n;
                $n++;
            }
            while(file_exists(ds($destination, $new_dirname)) && !is_dir(ds($destination, $new_dirname)));
            Log::info('Generated unique dirname: `{$new_dirname}`.', __FILE__, __LINE__);
        }

        return ds($destination, $new_dirname);
    }

    /**
     * Will produce md5 signature for the whole directory (and sub directories
     * if deep is true).
     * --
     * @param  string  $directory
     * @param  boolean $deep
     * --
     * @return string
     */
    public static function dir_signature($directory, $deep=true)
    {

    }

    public static function dir_signatures($directory, $deep=true) {}
    public static function dir_create($name, $recursive=true, $mode=0755) {}
    public static function dir_is_writable($directory) {}
    public static function dir_remove($directory) {}
    public static function dir_copy($source, $destination, $on_exists=self::REPLACE) {}
    public static function dir_move($source, $destination, $on_exists=self::REPLACE) {}
    public static function dir_is_public($filename) {}
    public static function dir_get_url($filename) {}
}
