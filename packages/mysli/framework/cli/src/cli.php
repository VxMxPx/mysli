<?php

namespace mysli\framework\cli;

__use(__namespace__, '
    mysli/framework/fs/{fs,file,dir}
    mysli/framework/type/arr
    mysli/framework/pkgm
');

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
        output::nl();
        output::line('* Mysli Cli');
        output::line('    To run a command use: ./dot <COMMAND> [OPTIONS...]');
        output::nl();
        output::line('* List of Available Commands:');
        output::line(arr::readable($commands, 4));
    }
    /**
     * Scan packages to find scripts.
     * @return array
     */
    private static function discover_scripts() {
        $scripts = [];
        foreach (pkgm::dump() as $package) {
            if (empty($package['sh'])) {
                continue;
            }
            //$files = scandir($path);
            foreach ($package['sh'] as $script) {
                $scripts[$script] = [
                    'package'     => $package['package'],
                    'description' => $package['description']
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
            output::line(output::yellow("Command not found: `{$script}`."));
            return false;
        }
        $namespace = str_replace(
            '/', '\\', $scripts[$script]['package']."/sh/{$script}");

        if (!function_exists($namespace.'\__init')) {
            $file = fs::pkgpath(
                $scripts[$script]['package'], 'sh', $script.'.php');

            if (file::exists($file)) {
                include $file;
            } else {
                output::error("[!] File not found: `{$file}`");
                return false;
            }
        }

        if (function_exists($namespace.'\__init')) {
            call_user_func_array($namespace.'\__init', [$arguments]);
        } else {
            output::format(
                '+yellow Method `__init` not found for `%s`.', $script);
        }
    }
}
