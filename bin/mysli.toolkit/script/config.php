<?php

namespace mysli\toolkit\root\script; class config
{
    const __use = '
        .{
            pkg
            type.arr   -> arr
            type.str   -> str
            config     -> lib\config
            cli.prog   -> prog
            cli.param  -> param
            cli.ui     -> ui
            cli.output -> output
        }
    ';

    /**
     * Configuration command line utility.
     * --
     * @param array $args
     * --
     * @return boolean
     */
    static function __run(array $args)
    {
        /*
        Set params.
         */
        $prog = new prog('Mysli Config', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.toolkit', true);

        $prog
        ->create_parameter('PACKAGE', [
            'help' => 'Package which will be affected. '.
                      'If not specified, all packagws will be listed.'
        ])
        ->create_parameter('KEY', [
            'help' => 'Configuration key which to get/set. '.
                      'If not specified, then all configurations '.
                      'for specified package will be listed.'
        ])
        ->create_parameter('VALUE', [
            'help' => 'Configuration value which will be set.'.
                      'If not specified, then current value will be displated.'
        ])
        ->create_parameter('--null', [
            'type'    => 'boolean',
            'def'     => false,
            'exclude' => [$prog->get_parameter('value')],
            'help'    => 'Force value to be `null` when setting.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        // Get parameters
        $package = $prog->get_parameter('package');
        $key     = $prog->get_parameter('key');
        $value   = $prog->get_parameter('value');
        $null    = $prog->get_parameter('--null');

        if ($package->is_set())
        {
            if (!lib\config::select($package->get_value()))
            {
                ui::error('ERROR', "Package not found: `{$package}`.");
                return false;
            }
        }

        // If no package is set, list of available packages will be displayed.
        if (!$package->is_set())
        {
            return static::get_list(false);
        }
        elseif (!$key->is_set())
        {
            return static::get_list($package->get_value());
        }
        elseif (!$value->is_set() && !$null->is_set())
        {
            return static::get_value($package->get_value(), $key->get_value());
        }
        else
        {
            return static::set_value(
                $package->get_value(),
                $key->get_value(),
                $value->is_set() ? $value->get_value() : null
            );
        }
    }

    /**
     * Get list of all packages with config
     * or config values for particular package.
     * --
     * @param string $package
     */
    static function get_list($package=null)
    {
        if ($package)
        {
            ui::title("Available options for `{$package}`");
            $options = lib\config::select($package)->as_array();

            if (empty($options))
            {
                ui::line("No options available.");
            }
            else
            {
                output::format(static::format_options($options));
            }
        }
        else
        {
            ui::title("Available packages");

            $list = lib\config::get_list();

            if (!empty($list))
            {
                ui::lst($list);
                ui::line(
                    "\nUse `mysli config vendor.package` to see all options ".
                    "for particular package.");
            }
            else
            {
                ui::line('No configuration available.');
            }
        }

        return true;
    }

    /**
     * Get value for package.
     * --
     * @param string $package
     * @param string $key
     */
    static function get_value($package, $key)
    {
        $options = lib\config::select($package)->as_array();

        if (!isset($options[$key]))
        {
            ui::warning('WARNING', "No such key: `{$key}`.");
            return false;
        }
        else
        {
            output::format(static::format_options([$key => $options[$key]]));
            return true;
        }
    }

    /**
     * Set value for package.
     * --
     * @param string  $package
     * @param string  $key
     * @param string  $value
     */
    static function set_value($package, $key, $value)
    {
        $config = lib\config::select($package);
        $type = $config->get_type($key);

        if (!$type)
        {
            ui::warning("WARNING", "Key not found: `{$key}`.");
            return false;
        }


        if ($value !== null)
        {
            switch ($type) {
                case 'boolean':
                    if (!in_array(strtolower($value), ['true', 'false']))
                        ui::warning(
                            'WARNING',
                            "Converting non boolean value: `{$value}` to boolean!");
                    $value = strtolower($value) === 'true';
                    break;

                case 'string':
                    $value = (string) $value;
                    break;

                case 'integer':
                    if (!is_numeric($value))
                        ui::warning(
                            'WARNING',
                            "Converting non numeric value: `{$value}` to integer!");
                    $value = (integer) $value;
                    break;

                case 'float':
                    if (!is_numeric($value))
                        ui::warning(
                            'WARNING',
                            "Converting non numeric value: `{$value}` to float!");
                    $value = (float) $value;
                    break;

                case 'numeric':
                    if (!is_numeric($value))
                        ui::warning(
                            'WARNING',
                            "Converting non numeric value: `{$value}` to number!");
                    if (strpos($value, '.') !== false)
                        $value = (float) $value;
                    else
                        $value = (integer) $value;
                    break;

                case 'array':
                    $value = str::split_trim($value, ',');
                    break;

                default:
                    ui::error(
                        'ERROR',
                        "Invalid type `{$type}` for key `{$key}`.");
                    return false;
            }
        }

        $config->set($key, $value);

        if ($config->save())
        {
            output::green("OK: ", false);
            static::get_value($package, $key);
            return true;
        }
        else
        {
            ui::error('ERROR', "Value couldn't be saved.");
            return false;
        }
    }

    /**
     * Prepare options array to be displayed in CLI.
     * --
     * @param  array  $options
     * --
     * @return string
     */
    private static function format_options(array $options)
    {
        $output  = "";
        $longest = 0;
        $longest_type = 0;

        // Find longest key to align values nucely
        foreach ($options as $key => list($type, $_))
        {
            if (strlen($key) > $longest)
                $longest = strlen($key);

            if (strlen($type) > $longest_type)
                $longest_type = strlen($type);
        }

        foreach ($options as $key => list($type, $value))
        {
            $output .= "\n".str_pad($key, $longest+1);
            $output .= str_pad($type, $longest_type+1);

            if ($value === null)
            {
                $output .= "<red>Null</red>";
                continue;
            }

            switch ($type) {
                case 'boolean':
                    $output .= $value ? '<green>True</green>' : '<red>False</red>';
                    continue;

                case 'string':
                    $output .= "<blue>\"{$value}\"</blue>";
                    continue;

                case 'integer':
                case 'float':
                case 'numeric':
                    $output .= "<yellow>{$value}</yellow>";
                    continue;

                case 'array':
                    $output .= "\n".arr::readable(
                        $value, 4, 4, ' : ', "\n", true
                    );
                    continue;
            }
        }

        return ltrim($output."\n", "\n");
    }
}
