<?php

namespace mysli\util\i18n\script;

__use(__namespace__, '
    ./i18n AS root/i18n
    mysli/framework/pkgm
    mysli/framework/fs/{fs,dir}
    mysli/framework/cli/{param,output} AS {param,cout}
');

class i18n {
    static function run($args) {
        $param = new param('Mysli Util I18n', $args);
        $param->command = 'i18n';
        $param->add('-w/--watch', [
            'help'    => 'Rebuild cache if changes occurs.',
            'type'    => 'bool',
            'default' => false
        ]);
        $param->add('-d/--directory', [
            'help'    => 'Costume translations directory.',
            'default' => 'i18n'
        ]);
        $param->add('PACKAGE', [
            'help'     => 'Package name, if not provided, '.
                          'current directory will be used.',
            'required' => false
        ]);

        $param->parse();
        if (!$param->is_valid()) {
            cout::line($param->messages());
            return;
        }
        $values = $param->values();
        if (!$values['package']) {
            $values['package'] = pkgm::name_from_path(getcwd());
        }
        if (!$values['package']) {
            cout::warn("Not a valid package, use `-h` for help");
            return;
        }
        if ($values['watch']) {
            self::watch($values['package'], $values['directory']);
        } else {
            self::build($values['package'], $values['directory']);
        }
    }

    private static function build($package, $directory='i18n') {
        if (!dir::exists(fs::pkgpath($package, $directory))) {
            cout::error('I18n: Not found: `' .
                        fs::ds($package, $directory) . '`');
            return;
        }
        if (root\i18n::create_cache($package, $directory)) {
            cout::format('I18n: %s +right+green OK', [$package]);
        } else {
            cout::format('I18n: %s +right+red FAILED', [$package]);
        }
    }
    private static function watch($package, $directory='i18n') {
        if (!dir::exists(fs::pkgpath($package, $directory))) {
            cout::error('I18n: Not found: `' .
                        fs::ds($package, $directory) . '`');
            return;
        } else {
            cout::info("I18n: Found {$directory} for {$package}, observing");
            cout::info("Press CTRL+C to quit.");
        }

        $dir = fs::pkgpath($package, $directory);
        $last_signature = implode('', dir::signature($dir));
        while (true) {
            $new_signature = implode('', dir::signature($dir));
            if ($last_signature != $new_signature) {
                $last_signature = $new_signature;
                cout::line('I18n: Changes detected, rebuilding...');
                self::build($package, $directory);
            }
            sleep(3);
        }
    }
}
