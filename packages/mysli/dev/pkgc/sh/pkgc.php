<?php

namespace mysli\dev\pkgc\script;

__use(__namespace__, '
    mysli/framework/pkgm
    mysli/framework/fs/{fs,file,dir}
    mysli/framework/cli/{param,output,input} AS {param,cout,cin}
    mysli/framework/exception/{...} AS exception/{...}
');

class pkgc {
/**
     * Execute script.
     * @param  array $args
     * @return null
     */
    static function run($args) {
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
            self::create($v['package']);
        }
    }
    /**
     * Handle action.
     * @param  array  $args
     * @return null
     */
    private static function create($package) {
        $path = fs::pkgpath($package);
        if (!$package) {
            cout::error("[!] Please specify a valid package name.");
            return;
        } elseif (!file::exists($path)) {
            cout::error("[!] Package not found: `{$package}`.");
            return;
        } else cout::line("* New release of `{$package}`");

        $meta = pkgm::meta($package, true);
        $new_version = self::increase_version($meta['version']);
        $new_version = cin::line(
            "[?] Previous version: {$meta['version']}; ".
            "enter a new version [{$new_version}]: ",
            function ($input) use ($new_version) {
                if ($input) {
                    if (preg_match('/[0-9]+\.[0-9]+\.[0-9]/', $input)) {
                        $new_version = $input;
                    } else {
                        cout::warn(
                            "[!] Please enter a valid version in format: ".
                            "`major.minor.bug`");
                        return;
                    }
                }
                return $new_version;
            });

        $package_full = str_replace('/', '.', $package);
        $package_full .= '-v'.$new_version;
        $tmp_dir = fs::tmppath('pkgc', $package_full);

        cout::line("\n* Creating release:");
        cout::line("    Release: {$package_full}");

        // Dir exists?
        if (dir::exists($tmp_dir)) {
            cout::line("    Temporariy directory exists, it will be deleted...", false);
            if (dir::remove($tmp_dir)) {
                cout::format('+green+right OK');
            } else {
                cout::format('+red+right FAILED');
                return false;
            }
        }
        cout::line("    Copying files...", false);
        if (dir::copy($path, $tmp_dir)) {
            cout::format('+green+right OK');
        } else {
            cout::format('+red+right FAILED');
            return false;
        }

        // Increase version in mysli.pkg.ym file
        cout::line("    Writting a new version...", false);
        try {
            self::write_version(
                fs::ds($tmp_dir, 'mysli.pkg.ym'),
                $meta['version'],
                $new_version);
        } catch (\Exception $e) {
            cout::format('+red+right FAILED');
            cout::line('    '.$e->getMessage());
            return false;
        }
        cout::format('+green+right OK');

        // Create phar archive
    }
    /**
     * Write new version to meta file.
     * @param  string $file
     * @param  string $old
     * @param  string $new
     */
    private static function write_version($file, $old, $new) {
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
    /**
     * Calculate new version from old.
     * @param  string $version
     * @return string
     */
    private static function increase_version($version) {
        $seg = explode('.', $version);
        $seg[2] = (int) $seg[2];
        $seg[2]++;
        return implode('.', $seg);
    }
}
