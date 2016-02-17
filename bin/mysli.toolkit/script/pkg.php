<?php

namespace mysli\toolkit\root\script; class pkg
{
    const __use = '
        .{ pkg -> lib\pkg, cli.prog -> prog, cli.param -> param, cli.ui -> ui }
    ';

    /**
     * Run pkg cli.
     * --
     * @param array $args
     */
    static function __run(array $args)
    {
        /*
        Set parameters.
         */
        $prog = new prog('Mysli Pkg', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.toolkit', true);
        $prog->set_description('Manage Mysli Packages.');

        $prog
        ->create_parameter('-e/--enable', [
            'help' => 'Enable a package',
        ])
        ->create_parameter('-d/--disable', [
            'exclude' => $prog->get_parameter('enable'),
            'help'    => 'Disable a package',
        ])
        ->create_parameter('--list', [
            'exclude' => $prog->get_parameters('enable', 'disable'),
            'help'    => 'Display a list of packages. Available options: '.
                         'all|enabled|disabled.',
        ])
        ->create_parameter('--meta', [
            'exclude' => $prog->get_parameters('enable', 'disable', 'list'),
            'help'    => 'Display meta information for particular package.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        $value = $prog->get_option_at(0, 'value');

        switch ($prog->get_option_at(0, 'name'))
        {
            case 'enable':
                return static::enable($value);

            case 'disable':
                return static::disable($value);

            case 'list':
                return static::do_list($value);

            case 'meta':
                return static::meta($value);

            default:
                ui::warning(
                    'WARNING',
                    'Invalid command, use --help to see available commands.');
                return false;
        }
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Enable particular package.
     * --
     * @param string $package
     * --
     * @return boolean
     */
    private static function enable($package)
    {
        ui::title("Enable: {$package}");

        try
        {
            $dependencies = lib\pkg::get_dependencies($package, true);

            if (count($dependencies['missing']))
            {
                ui::error('ERROR', 'Cannot proceed, packages are missing:');
                ui::lst($dependencies['missing']);
                return false;
            }
            if (count($dependencies['version']))
            {
                ui::error('ERROR', 'Cannot proceed, packages are at invalid version:');
                ui::lst($dependencies['version']);
                return false;
            }
            if (count($dependencies['disabled']))
            {
                ui::nl();
                ui::line('Following packages will also be enabled:');
                ui::lst($dependencies['disabled']);
                ui::nl();
            }

            lib\pkg::enable($package);
        }
        catch (\Exception $e)
        {
            ui::error("FAILED", $e->getMessage());
            return false;
        }

        ui::success('OK', "Enabled!");
        return true;
    }

    /**
     * Disable a particular package.
     * --
     * @param string $package
     * --
     * @return boolean
     */
    private static function disable($package)
    {
        ui::title("Disable: {$package}");

        try
        {
            $dependees = lib\pkg::get_dependees($package, true);

            if (count($dependees))
            {
                ui::nl();
                ui::line("Following packages will also be disabled:");
                ui::lst($dependees);
                ui::nl();
            }

            lib\pkg::disable($package);
        }
        catch (\Exception $e)
        {
            ui::error("FAILED", $e->getMessage());
            return false;
        }

        ui::success('OK', "Disabled!");
        return true;
    }

    /**
     * Print list of particular group of packages.
     * --
     * @param  string $type all|enabled|disabled
     * --
     * @throws mysli\toolkit\exception\pkg 10 Invalid type.
     * --
     * @return boolean
     */
    private static function do_list($type)
    {
        switch ($type) {
            case 'all':
                $list = lib\pkg::list_all();
            break;

            case 'enabled':
                $list = lib\pkg::list_enabled();
            break;

            case 'disabled':
                $list = lib\pkg::list_disabled();
            break;

            default:
                ui::warning(
                    'WARN', "Invalid type, please use: all|enabled|disabled");
                return false;
        }

        ui::title("List of {$type} packages.");
        ui::lst($list);
        return true;
    }

    /**
     * Print meta for particular package.
     * --
     * @param  string $package
     * --
     * @return boolean
     */
    private static function meta($package)
    {
        $meta = lib\pkg::get_meta($package);
        ui::title("Meta for {$package}");
        ui::lst($meta, ui::list_aligned);
        return true;
    }
}
