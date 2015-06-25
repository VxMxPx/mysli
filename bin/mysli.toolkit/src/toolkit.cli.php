<?php

namespace dot; class cli
{
    const __use = '
        .output
        mysli.toolkit.{fs,file,dir}
        mysli.toolkit.type.arr
    ';

    /**
     * Execute command.
     * --
     * @param  array $arguments
     * --
     * @return void
     */
    static function run(array $arguments)
    {
        if (isset($arguments[1]))
        {
            if ($arguments[1] === '--help')
            {
                self::list_scripts();
                exit(0);
            }

            $r = self::execute($arguments[1], array_slice($arguments, 2));

            if ($r === true)  { exit(0); }
            if ($r === false) { exit(1); }
        }
        else
        {
            self::list_scripts();
            exit(0);
        }
    }

    /**
     * List all available scripts.
     * --
     * @return null
     */
    static function list_scripts()
    {
        $commands = [];

        foreach (self::discover_scripts() as $script => $data)
        {
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
     * --
     * @return array
     */
    private static function discover_scripts()
    {
        $scripts = [];

        foreach (\core\pkg::dump()['pkg'] as $package)
        {
            if (empty($package['sh']))
            {
                continue;
            }

            foreach ($package['sh'] as $script)
            {
                $scripts[$script] = [
                    'package'     => $package['package'],
                    'description' => isset($package['description'])
                        ? $package['description']
                        : ''
                ];
            }
        }

        return $scripts;
    }

    /**
     * Execute a script.
     * --
     * @param  string $script
     * --
     * @return boolean
     */
    private static function execute($script, array $arguments=[])
    {
        $scripts = self::discover_scripts();

        if (!isset($scripts[$script]))
        {
            output::line(output::yellow("Command not found: `{$script}`."));
            return false;
        }

        $namespace  = str_replace('.', '\\', $scripts[$script]['package']);
        $namespace .= "\\sh\\{$script}";

        if (!class_exists($namespace, false))
        {
            $file = fs::pkgreal($scripts[$script]['package'], 'src/php/sh', $script.'.php');

            if (file::exists($file))
            {
                include $file;
            }
            else
            {
                output::error("[!] File not found: `{$file}`");
                return false;
            }
        }

        if (class_exists($namespace) && method_exists($namespace, '__init'))
        {
            return call_user_func_array($namespace.'::__init', [$arguments]);
        }
        else
        {
            output::format(
                "<yellow>Method `__init` not found for `%s`.\n", [$script]
            );
        }
    }
}