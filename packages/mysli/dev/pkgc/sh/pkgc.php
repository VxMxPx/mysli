<?php

namespace mysli\dev\pkgc\sh\pkgc;

__use(__namespace__, '
    mysli/web/assets
    mysli/framework/pkgm
    mysli/framework/fs/{fs,file,dir}
    mysli/framework/cli/{param,output,input} AS {param,cout,cin}
    mysli/framework/exception/{...}          AS exception/{...}
');

/**
 * Execute script.
 * @param  array $args
 * @return null
 */
function __init(array $args) {
    $param = new param('Mysli Package Creator', $args);
    $param->command = 'pkgc';
    $param->add('PACKAGE', [
        'help'     => 'Package name. If not provided, current '.
                      'directory will be used.',
        'required' => false,
        'default'  => null
    ]);

    $param->parse();
    if (!$param->is_valid()) {
        cout::line($param->messages());
    } else {
        $v = $param->values();
        if (!$v['package']) {
            $v['package'] = pkgm::name_from_path(getcwd());
        }
        create($v['package']);
    }
}
/**
 * Handle action.
 * @param  array $package
 * @return null
 */
function create($package) {

    $path = fs::pkgpath($package);

    // Check if we have a valid package
    if (!$package) {
        cout::error("[!] Please specify a valid package name.");
        return flase;
    } elseif (!file::exists($path)) {
        cout::error("[!] Package not found: `{$package}`.");
        return;
    } else cout::line("\n* New release of `{$package}`");

    // Get packag's meta, version and release
    $meta = pkgm::meta($package, true);
    $new_version = ask_for_version((int) $meta['version']);
    $release = ask_for_release();

    // Create filenames
    $pkg_filename = str_replace('/', '.', $package);
    $pkg_filename .= "-r{$release}.{$new_version}";
    $tmp_filename = sha1($pkg_filename);
    $tmp_fullpath = fs::tmppath('pkgc', $tmp_filename.'.phar');
    $rel_fullpath = fs::tmppath('pkgc', $pkg_filename.'.phar');
    $dev_fullpath = fs::tmppath('pkgc', $pkg_filename.'-dev.phar.gz');

    // Intro, let the user know which release we're creating
    cout::line("\n* Creating release:");
    cout::line("    {$pkg_filename}");

    clean_files([$tmp_fullpath, $dev_fullpath, $rel_fullpath]);

    // Create DEV archive
    $phar_dev = create_phar($tmp_fullpath, $pkg_filename.'-dev.phar') or exit;
    $phar_dev->buildFromDirectory($path);
    increase_version($tmp_fullpath, $meta['version'], $new_version) or exit;
    compress($phar_dev) or exit;
    cout::line("    Rename to {$pkg_filename}-dev.phar", false);
    try {
        file::rename($tmp_fullpath.'.gz', "{$pkg_filename}-dev.phar.gz");
        cout::format('+green+right OK');
    } catch (\Exception $e) {
        cout::format('+red+right FAILED');
        cout::line('    [!] '.$e->getMessage());
        return false;
    }
    print_signature($phar_dev);

    clean_files([$tmp_fullpath]);

    // Create RELEASE archive
    $phar_rel = create_phar($rel_fullpath, "{$pkg_filename}.phar") or exit;

    cout::line("    Adding files:");
    $ignore = generate_ignore_list($meta);
    fs::map($path, function ($apath, $rpath, $is_dir) use ($phar_rel, $ignore) {
        cout::line("        File: `{$rpath}`", false);
        if (substr(file::name($rpath, true), 0, 1) === '.') {
            cout::format('+yellow+right SKIP');
            return fs::map_continue;
        }
        if ($is_dir) {
            if (in_array($rpath.'/', $ignore)) {
                cout::format('+yellow+right IGNORED');
                return fs::map_continue;
            } else {
                $phar_rel->addEmptyDir($rpath);
                cout::format('+green+right DIR');
            }
        } else {
            if (in_array($rpath, $ignore)) {
                cout::format('+yellow+right IGNORED');
                return;
            } else {
                if (substr($rpath, -4) === '.php') {
                    $phar_rel->addFromString(
                        $rpath, php_strip_whitespace($apath));
                    cout::format('+green+right COMPRESSED');
                } else {
                    $phar_rel->addFile($apath, $rpath);
                    cout::format('+green+right FILE');
                }
            }
        }
    });

    increase_version($rel_fullpath, $meta['version'], $new_version);
    print_signature($phar_rel);
}

/**
 * Get version from user.
 * @param  integer $version
 * @return integer
 */
function ask_for_version($version) {
    return (int) cin::line(
        "[?] Enter a new version [{$version}]: ",
        function ($input) use ($version) {
            if ($input) {
                if (preg_match('/^\d+$/', $input)) {
                    return $input;
                } else {
                    cout::warn("[!] Version must be a valid number.");
                    return;
                }
            }
            return $version;
        });
}
/**
 * Get release from user.
 * @return integer
 */
function ask_for_release() {
    $release = gmdate('ymd').'00';
    return (int) cin::line(
        "[?] Release number [{$release}]: ",
        function ($input) use ($release) {
            if ($input) {
                if (preg_match('/^\d{8}$/', $input)) {
                    return $input;
                } else {
                    cout::warn(
                        '[!] A valid release must be an eight digit number.');
                    return;
                }
            }
            return $release;
        });
}
/**
 * Check if any temporary file already exists and remove it
 * @param  array  $files
 */
function clean_files(array $files) {
    cout::line('    Removing existing files...');
    foreach ($files as $file) {
        if (file::exists($file)) {
            cout::line("    Found: `".file::name($file, true)."`", false);
            if (file::remove($file)) {
                cout::format('+green+right OK');
            } else {
                cout::format('+red+right FAILED');
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
function create_phar($path, $filename, $stub=null) {
    cout::line("\n* Creating package: {$filename}");
    if (!$stub) {
        $stub = '<?php die(\'204 No Content.\'); __HALT_COMPILER(); ?>';
    }
    try {
        cout::line("    File {$filename}", false);
        $phar = new \Phar($path, 0, $filename);
        $phar->setStub($stub);
        cout::format('+green+right OK');
        return $phar;
    } catch (\Exception $e) {
        cout::format('+red+right FAILED');
        cout::line('    [!] '.$e->getMessage());
        return false;
    }
}
/**
 * Increase version if necessary.
 * @param  string  $filename
 * @param  integer $old
 * @param  integer $new
 * @return boolean
 */
function increase_version($filename, $old, $new) {
    // Increase version in mysli.pkg.ym file
    if ((int) $old === (int) $new) {
        return true;
    }
    cout::line("    Writing a new version", false);
    try {
        write_version("phar://{$filename}/mysli.pkg.ym", $old, $new);
        cout::format('+green+right OK');
        return true;
    } catch (\Exception $e) {
        cout::format('+red+right FAILED');
        cout::line('    [!] '.$e->getMessage());
        return false;
    }
}
/**
 * Compress package.
 * @param  \Phar  $phar
 * @return boolean
 */
function compress(\Phar $phar) {
    cout::line("    Compressing", false);
    try {
        $phar->compress(\Phar::GZ);
        cout::format('+green+right OK');
        return true;
    } catch (\Exception $e) {
        cout::format('+red+right FAILED');
        cout::line('    [!] '.$e->getMessage());
        return false;
    }
}
/**
 * Print phar's signature
 * @param  \Phar  $phar
 */
function print_signature(\Phar $phar) {
    $sig = $phar->getSignature();
    cout::line("    Signature {$sig['hash_type']}/{$sig['hash']}");
}
/**
 * Get list of files and directories to be ignored.
 * @global assets
 * @param  array $meta
 * @return array
 */
function generate_ignore_list($meta) {
    $ignore = [];

    // Big License
    $ignore[] = 'doc/COPYING';
    $ignore[] = 'tests/';

    // Find any internal ignores
    if (isset($meta['pkgc'])) {
        if (isset($meta['pkgc']['ignore']) &&
            is_array($meta['pkgc']['ignore']))
        {
            $ignore = array_merge($ignore, $meta['pkgc']['ignore']);
        }
    }
    // Check for i18n
    if (isset($meta['i18n']) && isset($meta['i18n']['source'])) {
        $ignore[] = rtrim($meta['i18n']['source'], '\\/').'/';
    } else {
        $ignore[] = 'i18n/';
    }

    // Check for tplp
    if (isset($meta['tplp']) && isset($meta['tplp']['source'])) {
        $ignore[] = rtrim($meta['tplp']['source'], '\\/').'/';
    } else {
        $ignore[] = 'tplp/';
    }

    // Assets
    list($as_src, $as_dest, $as_map) = assets::get_default_paths(
                                        $meta['package']);
    $ignore[] = $as_src.'/';
    $map = false;
    try {
        $map = assets::get_map($meta['package'], $as_src, $as_map);
    } catch (\Exception $e) {
        // Pass
    }
    if (is_array($map) && isset($map['files']) && is_array($map['files'])) {
        $extlist = is_array($map['settings'])
                && is_array($map['settings']['ext'])
                    ? $map['settings']['ext'] : [];

        foreach ($map['files'] as $file) {
            if (!is_array($file)) { continue; }
            if (isset($file['compress']) && $file['compress'] &&
                isset($file['include']) && is_array($file['include']))
            {
                foreach ($file['include'] as $include) {
                    $include = assets::parse_extention($include, $extlist);
                    $ignore[] = fs::ds($as_dest, $include);
                }
            }
        }
    }

    return $ignore;
}
/**
 * Write new version to meta file.
 * @param  string $file
 * @param  string $old
 * @param  string $new
 */
function write_version($file, $old, $new) {
    if (!file::exists($file)) {
        throw new exception\not_found("File not found: `{$file}`");
    }
    $r = 0;
    $meta = file::read($file);
    $meta = preg_replace(
        '/^(version[\t\ ]*?\:[\t\ ]*?)('.preg_quote($old).')$/m',
        '${1}'.$new,
        $meta, -1, $r);

    if ($r != 1) {
        throw new exception\data(
            "Could not change version in meta file ({$old} => {$new}), ".
            "replacement result is: `{$r}`, expected: `1`.");
    }

    file::write($file, $meta);
}

