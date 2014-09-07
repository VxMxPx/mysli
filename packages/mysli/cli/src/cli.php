<?php

namespace mysli\cli {

    \inject::to(__namespace__)
    ->from('mysli/core/type/arr')
    ->from('mysli/fs')
    ->from('mysli/pkgm');

    class cli {
        /**
         * Execute command
         * @param  array $arguments
         * @return void
         */
        static function run(array $arguments) {
            if (isset($arguments[1])) {
                if ($arguments[1] === '--help') {
                    self::list_scripts();
                }
                self::execute($arguments[1], array_slice($arguments, 2));
            } else {
                self::list_scripts();
            }
        }
        /**
         * List all available scripts.
         * @return null
         */
        static function list_scripts() {
            $commands = [];
            foreach (self::discover_scripts() as $script => $data) {
                $commands[$script] = $data['description'];
            }
            output::info('Mysli Cli. List of Available Commands:');
            output::info('<COMMAND> [OPTIONS...]');
            output::info(arr::readable($commands));
        }
        /**
         * Scan packages to find scripts.
         * @return array
         */
        private static function discover_scripts() {
            $scripts = [];

            foreach (pkgm::list_enabled() as $package) {
                $path = pkgpath($package . '/script');
                if (!fs\dir::exists($path)) {
                    continue;
                }
                $files = scandir($path);
                foreach ($files as $file) {
                    if (substr($file, -4) !== '.php') {
                        continue;
                    }
                    $id = substr($file, 0, -4);
                    $meta = pkgm::meta($package);
                    $scripts[$id] = [
                        'package'     => $package,
                        'script'      => fs\ds('script', $id),
                        'description' => $meta['description']
                    ];
                }
            }

            return $scripts;
        }
        /**
         * Execute a script.
         * @param  string $script
         * @return boolean
         */
        private static function execute($script, array $arguments=[]) {
            $scripts = self::discover_scripts();
            if (!isset($scripts[$script])) {
                output::warn('Command not found: `%s`.', $script);
                return false;
            }
            $script = $scripts[$script]['name'] . "/script/{$script}";

            if (method_exists($script, 'run')) {
                call_user_func_array([$script, 'run'], [$arguments]);
            } else {
                output::warn('Method `run` not found for: `%s`.', $script);
            }
        }
    }
}
