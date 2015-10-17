<?php

namespace mysli\toolkit\fs; class observer
{
    const __use = '
        .fs.{ fs, file, dir }
        .exception.{ observer }
    ';

    // Checksum methods
    const checksum_md5   = 'checksum_md5';
    const checksum_mtime = 'checksum_mtime';

    /**
     * Root folder to observe.
     * --
     * @var string
     */
    private $root;

    /**
     * File filter.
     * --
     * @var string
     */
    protected $filter = null;

    /**
     * Files and folders to be ignored.
     * --
     * @var array
     */
    protected $ignore = [ 'dist~/', '.git/', '.svg/' ];

    /**
     * How to vertify weather file was modified.
     * --
     * @var string
     */
    protected $checksum = self::checksum_md5;

    /**
     * Interval in seconds, to re-check files.
     * --
     * @var integer
     */
    protected $interval = 3;

    /**
     * Search sub directories.
     * --
     * @var boolean
     */
    protected $deep_scan = true;

    /**
     * List of signatures from last run.
     * --
     * @var array
     */
    protected $signatures = [];

    /**
     * Differences from last run.
     * --
     * @var array
     */
    protected $diff = [];

    /**
     * Instance.
     * --
     * @param  string $root Root folder to observe.
     */
    function __construct($root)
    {
        $this->set_root($root);
    }

    /**
     * Set root folder to be observed.
     * --
     * @param string $root
     * --
     * @throws mysli\toolkit\exception\observer 10 Directory not found.
     */
    function set_root($root)
    {
        if (dir::exists($root))
        {
            $this->root = $root;
        }
        else
        {
            throw new exception\observer("Directory not found: `{$root}`", 10);
        }
    }

    /**
     * Get root folder to be observed.
     * --
     * @param string $root
     */
    function get_root()
    {
        return $this->root;
    }

    /**
     * Filter can be in a regular expression format, and will be matched against
     * exact filename. Use `/regex/m` and specify exact filter which filename
     * must match.
     *
     * Simple filter is also supported. (@see fs::filter_to_regex())
     *
     * @example
     *
     *     dir::observe('/home/user', function ($changes)
     *     {
     *         echo "A file was changed.";
     *     }, '*.sh', 1);
     * --
     * @param  string $filter
     */
    function set_filter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Get filter.
     * --
     * @return string
     */
    function get_filter()
    {
        return $this->filter;
    }

    /**
     * Set files and folders to ignore:
     * folder/                Folder at any position
     * file.ext               File in any folder
     * /path/to/the/file.txt  Absolute (based on root) path to the file in dir
     * /folder/               Absolute (based on root) path to the folder
     * /file.ext              Absolute (based on root) path to the file
     * --
     * @param mixed   $ignore String (one item) or an array (multiple).
     * @param boolean $empty  Empty ignore list before inserting this item.
     */
    function set_ignore($ignore, $empty=false)
    {
        if ($empty)
        {
            $this->ignore = [];
        }

        if (!is_array($ignore))
        {
            $ignore = [ $ignore ];
        }

        $this->ignore = array_merge( $this->ignore, $ignore );
    }

    /**
     * Get list of ignored files and folders.
     * --
     * @return array
     */
    function get_ignore()
    {
        return $this->ignore;
    }

    /**
     * Set checksum method.
     * --
     * @param string $checksum See checksum_ constants.
     * --
     * @throws mysli\toolkit\exception\observer 10 Invalid checksum.
     */
    function set_checksum($checksum)
    {
        if (substr($checksum, 0, 9) !== 'checksum_')
            throw new exception\observer("Invalid checksum: `{$checksum}`", 10);

        $this->checksum = $checksum;
    }

    /**
     * Get checksum method.
     * --
     * @return string
     */
    function get_checksum()
    {
        return $this->checksum;
    }

    /**
     * Set interval in which to re-check files.
     * --
     * @param integer $interval
     */
    function set_interval($interval)
    {
        $interval = (int) $interval;

        if ($interval < 1)
            throw new exception\observer(
                "Inerval must be a positive number: `{$interval}`", 10
            );

        $this->interval = $interval;
    }

    /**
     * Get currently set interval.
     * --
     * @return integer
     */
    function get_interval()
    {
        return $this->interval;
    }

    /**
     * Set weather sub directories should be searched.
     * --
     * @param boolean $deep_scan
     */
    function set_deep_scan($deep_scan)
    {
        $this->deep_scan = !! $deep_scan;
    }

    /**
     * Get deep scan setting.
     * --
     * @return boolean
     */
    function get_deep_scan()
    {
        return $this->deep_scan;
    }

    /**
     * Set list of signatures to be used in future comparisons.
     * --
     * @param array $signatures
     */
    function set_signatures(array $signatures)
    {
        $this->signatures = $signatures;
    }

    /**
     * Get current list of signatures.
     * --
     * @return array
     */
    function get_signatures()
    {
        return $this->signatures;
    }

    /**
     * Get differences of last run.
     * --
     * @return array
     */
    function get_diff()
    {
        return $this->diff;
    }

    /**
     * Run once.
     *
     * @example
     *     $observer = new observer($path);
     *     $observer->set_signatures( $old_signatures );
     *     $observer->run();
     *     $diff = $observer->get_diff();
     * --
     * @return integer Number of changes.
     */
    function run()
    {
        // Reset diff...
        $this->diff = [];

        // Grab files...
        $sig_new = [];
        $files   = file::find($this->root, $this->filter, $this->deep_scan);

        if ($this->get_checksum() === static::checksum_mtime)
            clearstatcache();

        // Collect modification times
        foreach ($files as $file)
        {
            // TODO: Ignores!!

            $sig_new[$file] = $this->get_checksum() === static::checksum_mtime
                ? filemtime($file)
                : md5_file($file);
        }

        // Nothing to do here huh...
        if ($sig_new === [] || $sig_new === $this->signatures)
        {
            return 0;
        }

        foreach ($sig_new as $file => $signature)
        {
            if (!isset($this->signatures[$file]))
            {
                $this->diff[$file] = ['action' => 'added'];
            }
            elseif ($signature !== $this->signatures[$file])
            {
                $this->diff[$file] = ['action' => 'modified'];
            }
            // Nothing else to do here...
        }

        // Any files removed?
        foreach ($this->signatures as $file => $_)
        {
            if (!isset($sig_new[$file]))
            {
                $this->diff[$file] = ['action' => 'removed'];
            }
        }

        // Renamed...?
        foreach ($this->diff as $file => $opt)
        {
            if ($opt['action'] !== 'added')
            {
                continue;
            }

            $sig_add = $sig_new[$file];

            // Such thing was removed?
            if (($file_old = array_search($sig_add, $this->signatures)))
            {
                if (isset($this->diff[$file_old]) &&
                    $this->diff[$file_old]['action'] === 'removed')
                {
                    // Renamed or moved?
                    if ((dirname($file) !== dirname($file_old)) &&
                        (basename($file) === basename($file_old)))
                    {
                        $action = 'moved';
                    }
                    else
                    {
                        $action = 'renamed';
                    }

                    $this->diff[$file] = [
                        'action' => $action,
                        'from'   => $file_old
                    ];
                    $this->diff[$file_old] = [
                        'action' => $action,
                        'to'     => $file
                    ];
                }
            }
        }

        // Set new to old,...
        $this->signatures = $sig_new;

        return count( $this->diff );
    }

    /**
     * Observe path indefinitely.
     *
     * Callback will receive the list of changed files in format:
     *
     *     $changes = [
     *         '/full/absolute/path' => ['action' => added']
     *     ];
     *
     * Possible changes are: `added|removed|modified|renamed|moved`
     * In case of `renamed` and `moved` additional details will be set, either:
     * `from => file` or `to => file`.
     *
     * Two entries will be added in case of rename and move, one for an old
     * file and another for new. A new file will have key `from` and old file
     * `to`.
     *
     * !! Please note, this list does NOT contain directories.
     *
     * !! This function will run until something else than `null`
     * is returned by callback.
     *
     * If $for_each_file is set true, then parameters send to callback are:
     *
     *     string $filename -- Actual full path to the file.
     *     string $action   -- One of the actions listed above.
     *     array  $opt      -- Options (like from, to, ...)
     * --
     * @param  callable $callback
     *         Callback function.
     *
     * @param  boolean $for_each_file
     *         Call function for each changed file,
     * --
     * @return mixed
     */
    function observe($callback, $for_each_file=false)
    {
        // Go for it...
        do
        {
            $r = null;

            // Did run produce any changes?
            if ($this->run())
            {
                if ($for_each_file)
                {
                    foreach ($this->diff as $filename => $opt)
                    {
                        $r = $callback( $filename, $opt['action'], $opt );
                    }
                }
                else
                {
                    $r = $callback( $this->diff );
                }
            }

            if ($r !== null)
                return $r;

            // Take a nap...
            sleep($this->interval);

        } while ( true );
    }
}
