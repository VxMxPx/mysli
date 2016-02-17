<?php

namespace mysli\dev\pack\root\script; class pack
{
    const __use = <<<fin
        mysli.toolkit.{ ym, pkg, event }
        mysli.toolkit.fs.{ fs, dir, file }
        mysli.toolkit.cli.{ ui, prog, param, input, output }
fin;

    /**
     * Run.
     * --
     * @param array $args
     * --
     * @return boolean
     */
    static function __run(array $args)
    {
        $prog = new prog('Mysli Pack', __CLASS__);

        $prog->set_description('Command line utility for producing PHAR packages.');
        $prog->set_help(true);
        $prog->set_version('mysli.dev.pack', true);

        $prog
        ->create_parameter('--whitespace/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Do not remove whitespace from PHP files.'.
                    'Result in larger PHAR, but easier to debug.'
        ])
        ->create_parameter('--yes/-y', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Answer `yes` to all questions.'
        ])
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package for which PHAR should be created.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $whitespace, $yes) = $prog->get_values('package', '-w', '-y');
        return static::create($package, $whitespace, $yes);
    }

    /**
     * Create a new PHAR archive.
     * --
     * @param string  $package
     * @param boolean $whitespace
     * @param boolean $yes
     * --
     * @event mysli.dev.pack.root.script.pack::create.ignore_list
     *        ( string $package, array $ignore )
     *
     * @event mysli.dev.pack.root.script.pack::create.done
     *        ( string $package, \Phar $phar_instance )
     * --
     * @return boolean
     */
    protected static function create($package, $whitespace, $yes)
    {
        if (pkg::exists_as($package) !== pkg::source)
        {
            ui::error('ERROR', "Package is not valid: `{$package}`.");
            ui::line('Make sure package exists in source format.');
            return false;
        }

        // Package's root
        $pkg_root = fs::binpath($package);

        // Additional validation...
        if (!file::exists("{$pkg_root}/mysli.pkg.ym"))
        {
            ui::error('ERROR', "Not found: `{$pkg_root}/mysli.pkg.ym`.");
            return false;
        }

        // Get packag's meta
        $pkg_meta = pkg::get_meta($package);

        /*
        Ask for information
         */

        // Ask for version
        $pkg_api_version = ! $yes
            ? static::ask_for_version((int) $pkg_meta['version'])
            : (int) $pkg_meta['version'];

        // Ask for release
        $pkg_release = ! $yes
            ? static::ask_for_release(gmdate('ymd'))
            : gmdate('ymd');

        // Ask for pre-release
        $pkg_pre_release = ! $yes
            ? static::ask_for_pre_release('')
            : '';

        // Create filename
        $phar_name  = "{$package}-r{$pkg_release}.{$pkg_api_version}";
        $phar_name .= $pkg_pre_release ? "-{$pkg_pre_release}" : '';

        // Path where phar will be saved
        $phar_root = fs::ds($pkg_root, 'releases~');

        // Actual full pahr path (inc filename)
        $phar_afile = fs::ds($phar_root, $phar_name.'.phar');

        // Intro, let the user know which release we're creating
        ui::title("Creating release: {$phar_name}");

        // If not PHAR root exists, add it.
        if (!file::exists($phar_root))
        {
            if (dir::create($phar_root))
            {
                ui::success('CREATED', "Directory `{$phar_root}`.", 1);
            }
            else
            {
                ui::error("FAILED", "Cannot create directory `{$phar_root}`.", 1);
                return false;
            }
        }

        // If File exists, remove it
        if (file::exists($phar_afile))
        {
            ui::warning("File will be rewritten: `{$phar_afile}`", 1);
            file::remove($phar_afile);
        }

        // Check for stub
        if (file::exists("{$pkg_root}/__init.php"))
        {
            ui::success("FOUND", "Stub file.", 1);
            $phar_stub = file::read("{$pkg_root}/__init.php");
        }
        else
        {
            ui::info("NOT FOUND", "Stub file.", 1);
            $phar_stub = false;
        }

        // Create PHAR archive
        try
        {
            $phar_instance = static::create_phar(
                $phar_afile, "{$package}.phar", $phar_stub
            );
        }
        catch (\Exception $e)
        {
            ui::error("ERROR", $e->getMessage(), 1);
            return false;
        }

        if (!$phar_instance)
        {
            ui::error("ERROR", "Couldn't create PHAR.", 1);
            return false;
        }

        /*
        Start Adding Files
         */
        ui::line("ADDING FILES:");
        $ignore = static::generate_ignore_list($pkg_meta);

        // Local ignore files list
        $local_ignore = [];

        // Anyone is invited to add to the list...
        event::trigger(
            'mysli.dev.pack.root.script.pack::create.ignore_list',
            [$package, &$ignore]
        );

        fs::map(
            $pkg_root,
            function ($absolute_path, $relative_path, $is_dir)
            use ($phar_instance, $ignore, &$local_ignore, $whitespace)
        {
            if (substr(file::name($relative_path, true), 0, 1) === '.')
            {
                ui::info("SKIP", "Hidden file {$relative_path}", 1);
                return fs::map_continue;
            }

            foreach ($local_ignore as $lignore)
            {
                if (substr($absolute_path, 0, strlen($lignore)) === $lignore)
                {
                    if (strpos("{$absolute_path}/", '/dist~/'))
                    {
                        $relative_path = str_replace('/dist~/', '/', "/{$relative_path}/");
                        $relative_path = trim($relative_path, '/');
                        ui::info("UPDATED", "Relative path from dist~ {$relative_path}", 1);
                    }
                    else
                    {
                        ui::info("SKIP", "Directory is on local ignore list {$relative_path}", 1);
                        return fs::map_continue;
                    }
                }
            }

            // This directory contains dist~ directory
            if (dir::exists(fs::ds($absolute_path, 'dist~')))
            {
                ui::info("SKIP", "Directory added to local ignore list {$relative_path}", 1);
                // Skip the whole thing...
                $local_ignore[] = $absolute_path.'/';
                return;
            }

            if ($is_dir)
            {
                if (in_array($relative_path.'/', $ignore))
                {
                    ui::info("SKIP", "Directory is on ignore list {$relative_path}", 1);
                    return fs::map_continue;
                }
                else
                {
                    $phar_instance->addEmptyDir($relative_path);
                    ui::success("ADDED", "Directory {$relative_path}", 1);
                }
            }
            else
            {
                if (in_array($relative_path, $ignore))
                {
                    ui::info("SKIP", "File is on ignore list {$relative_path}", 1);
                    return;
                }
                else
                {
                    if (substr($relative_path, -4) === '.php' && !$whitespace)
                    {
                        $phar_instance->addFromString(
                            $relative_path, php_strip_whitespace($absolute_path)
                        );
                        ui::success("ADDED", "Compressed file {$relative_path}", 1);
                    }
                    else
                    {
                        $phar_instance->addFile($absolute_path, $relative_path);
                        ui::success("ADDED", "File {$relative_path}", 1);
                    }
                }
            }
        });

        // Write new api_version and release to phar file
        $meta_write = static::write_meta(
            "phar://".$phar_afile.'/mysli.pkg.ym',
            $pkg_api_version,
            $pkg_release,
            $pkg_pre_release
        );

        if (!$meta_write)
        {
            ui::error("ERROR", 'Failed to write meta.', 1);
            return false;
        }
        else
        {
            ui::success("OK", 'Meta file was written.', 1);
        }

        // Trigger done event
        event::trigger(
            'mysli.dev.pack.root.script.pack::create.done',
            [$package, $phar_instance]
        );

        // Print PHAR's signature
        $phar_signature = $phar_instance->getSignature();
        ui::nl();
        output::format(
            "<bold>Signature</bold> %s/%s\n",
            [ $phar_signature['hash_type'], $phar_signature['hash'] ]
        );

        return true;
    }

    /**
     * Get version from user.
     * --
     * @param integer $default
     * --
     * @return integer
     */
    private static function ask_for_version($default)
    {
        return (int) input::line(
            "Enter a new API version [{$default}]: ",
            function ($input) use ($default)
            {
                if ($input)
                {
                    if (preg_match('/^\d+$/', $input))
                    {
                        return $input;
                    }
                    else
                    {
                        ui::warning("Version must be a valid number.");
                        return;
                    }
                }

                return $default;
            }
        );
    }

    /**
     * Get a release from user.
     * --
     * @param integer $default
     * --
     * @return integer
     */
    static function ask_for_release($default)
    {
        $default = gmdate('ymd');

        return (int) input::line(
            "Release number [{$default}]: ",
            function ($input) use ($default)
            {
                if ($input)
                {
                    if (preg_match('/^\d{6}$/', $input))
                    {
                        return $input;
                    }
                    else
                    {
                        ui::warning(
                            'A valid release is required, '.
                            'it needs to be a six digit number.'
                        );
                        return;
                    }
                }

                return $default;
            }
        );
    }

    /**
     * Get pre-release from user.
     * --
     * @param string $default
     * --
     * @return string
     */
    static function ask_for_pre_release($default)
    {
        return input::line(
            "Enter pre-release version (alpha, beta, rc, ...) [{$default}]: ",
            function ($input) use ($default)
            {
                if ($input)
                {
                    if ($input && preg_match('/^[0-9A-Z]+$/i', $input))
                    {
                        return $input;
                    }
                    else
                    {
                        ui::warning(
                            'Pre-release consist only of alpha-numeric '.
                            '[0-9a-z] characters.'
                        );
                        return;
                    }
                }

                return $default;
            });
    }

    /**
     * Create a PHAR instance.
     * --
     * @param  string $path
     * @param  string $filename
     * @param  string $stub
     * --
     * @return \Phar
     */
    static function create_phar($path, $filename, $stub=null)
    {
        if (!$stub)
            $stub = '<?php die(\'204 No Content.\'); __HALT_COMPILER(); ?>';
        else
        {
            $stub = trim($stub);
            if (substr($stub, -2) !== '?>')
            {
                $stub .= "\n__HALT_COMPILER(); ?>";
            }
        }

        $phar = new \Phar($path, 0, $filename);
        $phar->setStub($stub);
        return $phar;
    }

    /**
     * Write new version and release to the meta file of a package.
     * --
     * @param string  $filename
     * @param string  $api_version
     * @param string  $release
     * @param string  $pre_release
     * --
     * @return boolean
     */
    static function write_meta($filename, $api_version, $release, $pre_release)
    {
        if (!file::exists($filename))
            return false;

        $meta = ym::decode_file($filename);
        $meta['version'] = $api_version;
        $meta['release'] = "r{$release}".($pre_release?"-{$pre_release}":'');

        return ym::encode_file($filename, $meta);
    }

    /**
     * Get list of files and directories to be ignored.
     * --
     * @param array $meta
     * --
     * @return array
     */
    static function generate_ignore_list(array $meta)
    {
        $ignore = [];

        // Big License
        $ignore[] = 'doc/';
        $ignore[] = 'tests/';
        $ignore[] = 'releases~/';

        if (isset($meta['pack']))
        {
            if (isset($meta['pack']['ignore']) && is_array($meta['pack']['ignore']))
            {
                $ignore = array_merge($ignore, $meta['pack']['ignore']);
            }
        }

        return $ignore;
    }
}
