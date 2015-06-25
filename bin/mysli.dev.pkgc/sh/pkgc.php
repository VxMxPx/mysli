<?php

namespace mysli\dev\pkgc\sh; class pkgc
{
    const __use = '
        mysli.toolkit.{ym,pkgm,event}
        mysli.toolkit.fs.{fs,file,dir}
        mysli.toolkit.exception.* -> toolkit.exception.*
        dot.{param,output,input} -> param,cout,cin
    ';

    /**
     * Execute script.
     * @param  array $args
     * @return null
     */
    static function __init(array $args)
    {
        $param = new param('Mysli Package Creator', $args);
        $param->command = 'pkgc';
        $param->add('--stub/-s', [
            'type'    => 'str',
            'help'    => 'Specify a costume stub file, relative to the package root, '.
                         'e.g.: src/php/stub.php'
        ]);
        $param->add('--whitespace/-w', [
            'type'    => 'bool',
            'default' => false,
            'help'    => 'Do not remove whitespace from PHP files, '.
                         'result in larger PHAR, but easier to debug.'
        ]);
        $param->add('--yes/-y', [
            'type'    => 'bool',
            'default' => false,
            'help'    => 'Answer to all questions with yes.'
        ]);
        $param->add('PACKAGE', [
            'required' => false,
            'default'  => null,
            'help'     => 'Package name. If not provided, current '.
                          'directory will be used.'
        ]);

        $param->parse();

        if (!$param->is_valid())
        {
            cout::line($param->messages());
        }
        else
        {
            $v = $param->values();

            if (!$v['package'])
            {
                $v['package'] = pkgm::name_by_path(getcwd());
            }

            self::create($v['package'], $v['stub'], $v['whitespace'], $v['yes']);
        }
    }
    /**
     * Handle action.
     * @param  array   $package
     * @param  string  $stub
     * @param  boolean $whitespace
     * @param  boolean $yes
     * @return null
     */
    static function create($package, $stub, $whitespace, $yes)
    {
        // Require source path!
        $path = fs::pkgpath(str_replace('.', '/', $package));

        // Check if we have a valid package
        if (!$package)
        {
            cout::error("[!] Please specify a valid package name.");
            return false;
        }
        elseif (!file::exists($path))
        {
            cout::error("[!] Package not found: `{$package}`.");
            return;
        }
        else
        {
            cout::line("\n* New release of `{$package}`");
        }

        // Get packag's meta, version and release
        $meta = pkgm::meta($package, true);
        $api_version = ! $yes
                            ? self::ask_for_version((int) $meta['version'])
                            : (int) $meta['version'];
        $release     = ! $yes
                            ? self::ask_for_release(gmdate('ymd'))
                            : gmdate('ymd');
        $pre_release = ! $yes
                            ? self::ask_for_pre_release('')
                            : '';

        // Create filenames
        $pkg_filename = str_replace('/', '.', $package);
        // $pkg_filename .= "-r{$release}.{$api_version}";
        // $pkg_filename .= $pre_release ? "-{$pre_release}" : '';
        $pkg_fullpath = fs::tmppath('pkgc', $pkg_filename.'.phar');

        // Intro, let the user know which release we're creating
        cout::line("\n* Creating release:");
        cout::line("    {$pkg_filename}");

        self::clean_files([$pkg_fullpath]);

        // Resolve stub path
        if ($stub)
        {
            if (file::exists(fs::ds($path, $stub)))
            {
                $stubc = file::read(fs::ds($path, $stub));
                cout::line("    Stub file found: `{$stub}`");
            }
            else
            {
                cout::err("    [!] Stub file not found: `{$stub}`");
                return false;
            }
        }
        else
        {
            $stubc = false;
        }

        // Create PHAR archive
        $phar = self::create_phar($pkg_fullpath, "{$pkg_filename}.phar", $stubc) or exit();

        cout::line("    Adding files:");
        $ignore = self::generate_ignore_list($meta);

        event::trigger('mysli.dev.pkgc/ignore_list', [$package, &$ignore]);

        // handle_assets($package);

        if ($stub)
        {
            $ignore[] = $stub;
        }

        fs::map($path, function ($apath, $rpath, $is_dir) use ($phar, $ignore, $whitespace)
        {
            cout::line("        File: `{$rpath}`", false);

            if (substr(file::name($rpath, true), 0, 1) === '.')
            {
                cout::format("<yellow><right>SKIP\n");
                return fs::map_continue;
            }

            if ($is_dir)
            {
                if (in_array($rpath.'/', $ignore))
                {
                    cout::format("<yellow><right>IGNORED\n");
                    return fs::map_continue;
                }
                else
                {
                    $phar->addEmptyDir($rpath);
                    cout::format("<green><right>DIR\n");
                }
            }
            else
            {
                if (in_array($rpath, $ignore))
                {
                    cout::format("<yellow><right>IGNORED\n");
                    return;
                }
                else
                {
                    if (substr($rpath, -4) === '.php' && !$whitespace)
                    {
                        $phar->addFromString($rpath, php_strip_whitespace($apath));
                        cout::format("<green><right>COMPRESSED\n");
                    }
                    else
                    {
                        $phar->addFile($apath, $rpath);
                        cout::format("<green><right>FILE\n");
                    }
                }
            }
        });

        // Write new api_version and release to phar file
        self::write_meta(
            "phar://".$pkg_fullpath.'/mysli.pkg.ym',
            $api_version,
            $release,
            $pre_release
        );

        event::trigger('mysli.dev.pkgc/done', [$package, &$phar]);

        // Re-inser asset's map
        // add_assets_map("phar://".$pkg_fullpath, $meta);

        // Finally print phar's signature
        self::print_signature($phar);
    }

    /**
     * Get version from user.
     * @param  integer $default
     * @return integer
     */
    static function ask_for_version($default)
    {
        return (int) cin::line(
            "[?] Enter a new api version [{$default}]: ",
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
                        cout::warn("[!] Version must be a valid number.");
                        return;
                    }
                }

                return $default;
            }
        );
    }
    /**
     * Get release from user.
     * @param  integer $default
     * @return integer
     */
    static function ask_for_release($default)
    {
        $default = gmdate('ymd');

        return (int) cin::line(
            "[?] Release number [{$default}]: ",
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
                        cout::warn(
                            '[!] A valid release must be an six digit number.');
                        return;
                    }
                }

                return $default;
            }
        );
    }
    /**
     * Get pre-release from user.
     * @param  string $default
     * @return string
     */
    static function ask_for_pre_release($default)
    {
        return cin::line(
            "[?] Enter pre-release version (alpha, beta, rc, ...) [{$default}]: ",
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
                        cout::warn(
                            '[!] Pre-release consist only of alpha-numeric '.
                            '[0-9a-z] characters.'
                        );
                        return;
                    }
                }

                return $default;
            });
    }
    /**
     * Check if any temporary file already exists and remove it
     * @param  array  $files
     */
    static function clean_files(array $files)
    {
        cout::line('    Removing existing files...');

        foreach ($files as $file)
        {
            if (file::exists($file))
            {
                cout::line("    Found: `".file::name($file, true)."`", false);

                if (file::remove($file))
                {
                    cout::format("<green><right>OK\n");
                }
                else
                {
                    cout::format("<red><right>FAILED\n");
                }
            }
        }
    }
    /**
     * Try to cerate phar.
     * @param  string $path
     * @param  string $filename
     * @param  string $stub
     * @return boolean
     */
    static function create_phar($path, $filename, $stub=null)
    {
        cout::line("\n* Creating package: {$filename}");

        if (!$stub)
        {
            $stub = '<?php die(\'204 No Content.\'); __HALT_COMPILER(); ?>';
        }

        try
        {
            cout::line("    File {$filename}", false);
            $phar = new \Phar($path, 0, $filename);
            $phar->setStub($stub);
            cout::format("<green><right>OK\n");
            return $phar;
        }
        catch (\Exception $e)
        {
            cout::format("<red><right>FAILED\n");
            cout::line('    [!] '.$e->getMessage());
            return false;
        }
    }
    /**
     * Increase version if necessary.
     * @param  string  $filename
     * @param  string  $api_version
     * @param  string  $release
     * @param  string  $pre_release
     * @return boolean
     */
    static function write_meta($filename, $api_version, $release, $pre_release)
    {
        cout::line("    Writing a new meta", false);

        if (!file::exists($filename))
        {
            cout::line("    [!] Failed! File not found: `{$filename}`.");
            return false;
        }

        $meta = ym::decode_file($filename);
        $meta['version'] = $api_version;
        $meta['release'] = "r{$release}".($pre_release?"-{$pre_release}":'');

        return ym::encode_file($filename, $meta);
    }
    /**
     * Print phar's signature
     * @param  \Phar  $phar
     */
    static function print_signature(\Phar $phar)
    {
        $sig = $phar->getSignature();
        cout::line("    Signature {$sig['hash_type']}/{$sig['hash']}");
    }
    /**
     * Get list of files and directories to be ignored.
     * @param  array $meta
     * @return array
     */
    static function generate_ignore_list(array $meta)
    {
        $ignore = [];

        // Big License
        $ignore[] = 'doc/COPYING';
        $ignore[] = 'tests/';

        if (isset($meta['pkgc']))
        {
            if (isset($meta['pkgc']['ignore']) && is_array($meta['pkgc']['ignore']))
            {
                $ignore = array_merge($ignore, $meta['pkgc']['ignore']);
            }
        }

        return $ignore;
    }
}