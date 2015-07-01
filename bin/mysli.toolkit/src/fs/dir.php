<?php

namespace mysli\toolkit\fs; class dir
{
    const __use = '.{log, exception.dir -> exception.dir}';

    /**
     * Create a new directory.
     * --
     * @throws mysli\toolkit\exception\dir 10 Directory/file already exists.
     * --
     * @param string  $directory
     * @param integer $mode
     * @param boolean $recursive
     * --
     * @return boolean
     */
    static function create($directory, $mode=0777, $recursive=true)
    {
        if (dir::exists($directory))
        {
            return true;
        }

        if (file::exists($directory))
            throw new exception\dir(
                "Directory/file already exists: `{$directory}`.", 10
            );

        log::info("Create: `{$directory}`, recursive: {$recursive}", __CLASS__);

        return mkdir($directory, $mode, $recursive);
    }

    /**
     * Copy a directory and all the content to the destination.
     * If destination doesn't exists, it will be created.
     * --
     * @throws mysli\toolkit\exception\dir
     *         10 Not a valid directory.
     *
     * @throws mysli\toolkit\exception\dir
     *         20 Cannot create destination directory.
     * --
     * @param  string  $source
     * @param  string  $destination
     * @param  boolean $recursive   Copy sub-directories.
     * @param  boolean $overwrite   If destination (file!) exists overwrite it.
     * --
     * @return integer Number of copied files and directories.
     */
    static function copy($source, $destination, $recursive=true, $overwrite=true)
    {
        $count = 0; // number of copied files and directories

        if (!self::exists($source))
        {
            throw new exception\dir(
                "Not a valid directory: `{$source}`.", 10
            );
        }

        if (!self::exists($destination))
        {
            if (!self::create($destination))
            {
                throw new exception\dir(
                    "Cannot create destination directory: ".
                    "`{$destination}`.", 20
                );
            }
            else
            {
                $count++;
            }
        }

        $files = array_diff(scandir($source), ['.', '..']);

        foreach ($files as $file)
        {
            $filename = fs::ds($source, $file);
            if (self::exists($filename))
            {
                if ($recursive)
                {
                    $count += self::copy(
                        $filename,
                        fs::ds($destination, $file),
                        $recursive,
                        $overwrite
                    );
                }
            }
            else
            {
                try
                {
                    log::info(
                        "Copy: `{$filename}` to directory: `{$destination}` ".
                        "as: `{$file}`, overwrite: `{$overwrite}`.",
                        __CLASS__
                    );

                    $count += file::copy(
                        $filename,
                        fs::ds($destination, $file),
                        $overwrite
                    );
                }
                catch (exception\argument $e)
                {
                    log::warning(
                        'Copying failed, with message: `{message}`.',
                        [__CLASS__, 'exception' => $e]
                    );
                }
            }
        }

        return $count;
    }

    /**
     * Move directory and all the content to the destination.
     * If destination doesn't exists, it will be created.
     * --
     * @throws mysli\toolkit\exception\dir
     *         10 Not a valid directory.
     *
     * @throws mysli\toolkit\exception\dir
     *         20 Cannot create destination directory.
     * --
     * @param  string  $source
     * @param  string  $destination
     * @param  boolean $overwrite   If destination (file!) exists overwrite it.
     * --
     * @return integer Number of moved files and directories.
     */
    static function move($source, $destination, $overwrite=true)
    {
        $count = 0; // number of moved files and directories

        if (!self::exists($source))
        {
            throw new exception\dir(
                "Not a valid directory: `{$source}`.", 10
            );
        }

        if (!self::exists($destination))
        {
            if (!self::create($destination))
            {
                throw new exception\dir(
                    "Cannot create destination directory: ".
                    "`{$destination}`.", 20
                );
            }
            else
            {
                $count++;
            }
        }

        $files = array_diff(scandir($source), ['.', '..']);

        foreach ($files as $file)
        {
            $filename = fs::ds($source, $file);

            if (self::exists($filename))
            {
                $count += self::move(
                    $filename,
                    fs::ds($destination, $file),
                    $overwrite
                );
            }
            else
            {
                try
                {
                    log::info(
                        "Move: `{$filename}` to directory: `{$destination}` ".
                        "as: `{$file}`, overwrite: `{$overwrite}`.",
                        __CLASS__
                    );

                    $count += file::move(
                        $filename,
                        fs::ds($destination, $file),
                        $overwrite
                    );
                }
                catch (exception\argument $e)
                {
                    log::warning(
                        'Moving failed, with message: `{message}`.',
                        [__CLASS__, 'exception' => $e]
                    );
                }
            }
        }

        try
        {
            self::remove($source, false);
        }
        catch (exception\fs $e)
        {
            log::warning(
                'Failed to remove source `{$source}`: `{message}`.',
                [__CLASS__, 'exception' => $e]
            );
        }

        return $count;
    }

    /**
     * Removed a directory.
     * --
     * @throws mysli\toolkit\exception\dir
     *         10 Argument cannot be empty or `/`.
     *
     * @throws mysli\toolkit\exception\dir
     *         20 Directory is not empty, use $force flag.
     *
     * @throws mysli\toolkit\exception\dir
     *         30 Could not remove file.
     *
     * @throws mysli\toolkit\exception\dir
     *         40 Could not remove directory.
     * --
     * @param string $directory
     *        Need to exists, cannot be empty string and cannot be root '/'.
     *
     * @param boolean $force
     *        Remove non empty directory.
     * --
     * @return boolean
     */
    static function remove($directory, $force=true)
    {
        if (!$directory || empty($directory) || trim($directory) === '/')
        {
            throw new exception\dir(
                'Argument $directory cannot be empty or /.', 10
            );
        }

        if (!self::exists($directory))
        {
            log::notice(
                "Cannot remove, directory doesn't exists: `{$directory}`.",
                __CLASS__
            );
            return true;
        }

        if (!self::is_empty($directory))
        {
            if (!$force)
            {
                throw new exception\dir(
                    'Directory is not empty, use $force flag.', 20
                );
            }
            $files = array_diff(scandir($directory), ['.','..']);

            foreach ($files as $file)
            {
                $filename = fs::ds($directory, $file);

                if (self::exists($filename))
                {
                    self::remove($filename, $force);
                    continue;
                }

                if (!unlink($filename))
                {
                    throw new exception\dir(
                        "Could not remove file: `{$filename}`.", 30
                    );
                }
            }
        }

        if (!rmdir($directory))
        {
            throw new exception\dir(
                "Could not remove directory: `{$directory}`.", 40
            );
        }
        else
        {
            return true;
        }
    }

    /**
     * Get signatures of all files in the directory +
     * sub directories if $deep is true.
     * --
     * @throws mysli\toolkit\exception\dir 10 Invalid directory,
     * --
     * @param string  $directory
     * @param boolean $deep
     * @param boolean $ignore_hidden Ignore hidden files and folders.
     * --
     * @return array
     */
    static function signature($directory, $deep=true, $ignore_hidden=true)
    {
        if (!self::exists($directory))
        {
            throw new exception\dir(
                "Invalid directory: `{$directory}`.", 10
            );
        }

        $result = [];
        $files = array_diff(scandir($directory), ['.','..']);

        foreach ($files as $file)
        {
            if ($ignore_hidden && substr($file, 0, 1) === '.')
            {
                continue;
            }

            $filename = fs::ds($directory, $file);

            if (self::exists($filename))
            {
                $result = array_merge($result, self::signature($filename));
            }
            else
            {
                $result[$filename] = file::signature($filename);
            }
        }

        return $result;
    }

    /**
     * Check weather directory is readable.
     * --
     * @param string $directory
     * --
     * @return boolean
     */
    static function is_readable($directory)
    {
        return is_readable($directory);
    }

    /**
     * Check if there are files in the directory.
     * --
     * @throws mysli\toolkit\exception\dir 10 The directory is not readable!
     * --
     * @param string $directory
     * --
     * @return boolean
     */
    static function is_empty($directory)
    {
        if (!self::is_readable($directory))
        {
            throw new exception\dir(
                'The directory is not readable!', 10
            );
        }

        $handle = opendir($directory);

        while (false !== ($entry = readdir($handle)))
        {
            if ($entry !== '.' && $entry !== '..')
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Return directory size in bytes.
     * --
     * @throws mysli\toolkit\exception\dir 10 Directory not found.
     * --
     * @param string  $directory
     * @param boolean $deep      Weather to include sub-directories.
     * --
     * @return integer
     */
    static function size($directory, $deep=true)
    {
        if (!self::exists($directory))
        {
            throw new exception\dir(
                "Directory not found: `{$directory}`.", 10
            );
        }

        $files = array_diff(scandir($directory), ['.','..']);
        $size = 0;

        foreach ($files as $file)
        {
            if (self::exists(fs::ds($directory, $file)))
            {
                if ($deep)
                {
                    $size += self::size(fs::ds($directory, $file));
                }
            }
            else
            {
                $size += file::size(fs::ds($directory, $file));
            }
        }

        return $size;
    }

    /**
     * Check if directory exists.
     * --
     * @param string $directory
     * --
     * @return boolean
     */
    static function exists($directory)
    {
        return file_exists($directory) && is_dir($directory);
    }
}
