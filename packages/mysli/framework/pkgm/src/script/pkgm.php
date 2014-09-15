<?php

namespace mysli\framework\pkgm\script {

    __use(__namespace__,
        ['./pkgm' => 'mpkgm'],
        ['../cli/{param,output}' => 'param,cout'],
        '../type/arr'
    );

    class pkgm {
        static function run($args) {
            $param = new param('Mysli Pkgm', $args);
            $param->command = 'pkgm';
            $param->description = 'Manage Mysli Packages.';
            $param->add('--repair', [
                'help'   => 'Scan and repair (if needed) packages database.',
                'type'   => 'bool',
                'invoke' => __namespace__.'\\pkgm::repair'
            ]);
            $param->add('-e/--enable', [
                'help' => 'Enable a package',
                'invoke' => __namespace__.'\\pkgm::enable'
            ]);
            $param->add('-d/--disable', [
                'help' => 'Disable a package',
                'invoke' => __namespace__.'\\pkgm::disable'
            ]);
            $param->parse();
            if (!$param->is_valid()) {
                cout::line($param->messages());
            }
        }
        static function repair() {
            cout::line('Repair...');
            foreach (mpkgm::list_enabled() as $package) {
                cout::line("Checking: `{$package}`", false);
                $dependencies = mpkgm::list_dependencies($package);
                if (empty($dependencies['disabled'])
                    && empty($dependencies['missing'])) {
                    cout::format('+right +green Nothing to do');
                }
                if (!empty($dependencies['disabled'])) {
                    foreach ($dependencies['disabled'] as $ddep => $vel) {
                        self::enable($ddep);
                    }
                }
                if (!empty($dependencies['missing'])) {
                    cout::format(
                        "+redMissing: \n%s\n+redCannot proceed...",
                        arr::readable($dependencies['missing'], 4));
                }
            }
        }
        static function enable($package) {
            cout::line("Will enable: `{$package}`", false);
            if (mpkgm::enable($package)) {
                cout::format('+right+green Done!');
            } else {
                cout::format('+right+red Failed!');
            }
        }
        static function disable() {
            cout::line("Will disable: `{$package}`");
            if (mpkgm::disable($package)) {
                cout::format('+right+green Done!');
            } else {
                cout::format('+right+red Failed!');
            }
        }
    }
}
