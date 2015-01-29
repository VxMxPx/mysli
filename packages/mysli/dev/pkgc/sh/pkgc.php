<?php

namespace mysli\dev\pkgc\sh\pkgc;

__use(__namespace__, '
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

    if (!$package) {
        cout::error("[!] Please specify a valid package name.");
        return;
    } elseif (!file::exists($path)) {
        cout::error("[!] Package not found: `{$package}`.");
        return;
    } else cout::line("\n* New release of `{$package}`");

    $meta = pkgm::meta($package, true);
    $new_version = (int) ($meta['version']);
    $new_version = cin::line(
        "[?] Enter a new version [{$new_version}]: ",
        function ($input) use ($new_version) {
            if ($input) {
                if (preg_match('/^\d+$/', $input)) {
                    $new_version = $input;
                } else {
                    cout::warn("[!] Version must be a valid number.");
                    return;
                }
            }
            return $new_version;
        });

    $release = gmdate('ymd').'00';
    $release = cin::line(
        "[?] Release number [{$release}]: ",
        function ($input) use ($release) {
            if ($input) {
                if (preg_match('/^\d{8}$/', $input)) {
                    $release = $input;
                } else {
                    cout::warn(
                        '[!] Please enter a valid release '.
                        'which must be an eight digit number.');
                    return;
                }
            }
            return $release;
        });

    $package_full = str_replace('/', '.', $package);
    $package_full .= "-r{$release}.{$new_version}";
    $tmp_dir = fs::tmppath('pkgc', $package_full);

    cout::line("\n* Creating release:");
    cout::line("    Release: {$package_full}");

    // Dir exists?
    if (dir::exists($tmp_dir)) {
        cout::line(
            "    Temporary directory exists, it will be deleted", false);
        if (dir::remove($tmp_dir)) {
            cout::format('+green+right OK');
        } else {
            cout::format('+red+right FAILED');
            return false;
        }
    }

    cout::line("    Copying files", false);
    if (dir::copy($path, $tmp_dir)) {
        cout::format('+green+right OK');
    } else {
        cout::format('+red+right FAILED');
        return false;
    }

    // Increase version in mysli.pkg.ym file
    if ((int) $meta['version'] !== (int) $new_version) {
        cout::line("    Writing a new version", false);
        try {
            write_version(
                fs::ds($tmp_dir, 'mysli.pkg.ym'),
                $meta['version'],
                $new_version);
            cout::format('+green+right OK');
        } catch (\Exception $e) {
            cout::format('+red+right FAILED');
            cout::line('    '.$e->getMessage());
            return false;
        }
    }

    // Create PHAR archive // dev version
    cout::line("    Creating dev package: {$package_full}-dev.phar", false);
    try {
        $devfn = "{$package_full}-dev.phar";
        $dev_phar = new \Phar(fs::tmppath('pkgc', $devfn), 0, $devfn);
        $dev_phar->buildFromDirectory($tmp_dir);
        $dev_phar->compress(\Phar::GZ);
        cout::format('+green+right OK');
    } catch (\Exception $e) {
        cout::format('+red+right FAILED');
        cout::line('    '.$e->getMessage());
        return false;
    }

    // Remove temp directory
    cout::line("\n* Cleaning up");
    cout::line('    Removing a temporary directory', false);
    try {
        dir::remove($tmp_dir);
        cout::format('+green+right OK');
    } catch (\Exception $e) {
        cout::format('+red+right FAILED');
        cout::line('    '.$e->getMessage());
        return false;
    }
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

