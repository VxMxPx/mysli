<?php

namespace mysli\toolkit\cli; class config
{
    const __use = '
        .{
            pkg,
            config -> root\config,
            fs.*
        }
        dot.{prog,param,ui}
    ';

    /**
     * Configuration command line utility.
     * --
     * @param array $args
     */
    static function __run(array $args)
    {
        /*
        Set params.
         */
        $prog = new prog('Mysli Config', '', 'config');
        $prog
        ->create_parameter('PACKAGE', [
            'help' => 'Package which will be affected. If no key then list all values.'
        ])
        ->create_parameter('KEY', [
            'help' => 'Configuration key which to get/set. If not VALUE, then show current value.'
        ])
        ->create_parameter('VALUE', [
            'help' => 'Configuration value which will be set.'
        ])
        ->create_parameter('--string', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Force value to be string when setting.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        // Get parameters
        $package = $prog->get_parameter('package');
        $key     = $prog->get_parameter('key');
        $value   = $prog->get_parameter('value');
        $string  = $prog->get_parameter('--string');

        // If no package is set, list of available packages will be displayed.
        if (!$package->is_set())
        {
            self::get_list(false);
        }
        else
        {
            // Weather to get or set.
            if (!$key->is_set())
            {
                self::get_list($package->get_value());
            }
            else
            {
                if (!$value->is_set())
                {
                    self::get_value($package->get_value(), $key->get_value());
                }
                else
                {
                    self::set_value(
                        $package->get_value(),
                        $key->get_value(),
                        $value->get_value(),
                        $string->get_value()
                    );
                }
            }
        }
    }

    /**
     * Get value for package.
     * --
     * @param string $package
     * @param string $key
     */
    static function get_value($package, $key)
    {
        $values = root\config::select($package, $key);
        $values = [$key => $values];
        ui::nl();
        ui::al($values);
    }

    /**
     * Set value for package.
     * --
     * @param string  $package
     * @param string  $key
     * @param string  $value
     * @param boolean $is_string
     *        Force value to be string. If this if false, numeric and boolean
     *        (e.g. True, False) values will be converted to apropriate type.
     */
    static function set_value($package, $key, $value, $is_string)
    {
        if (!pkg::is_enabled($package))
        {
            ui::error('ERROR', "Package is not enabled: `{$package}`");
            return;
        }

        $original_value = $value;

        // If string is not focrd,
        // then values should be converted to actual type.
        if (!$is_string)
        {
            if (is_numeric($value))
            {
                if (strpos($value, '.'))
                    $value = (float) $value;
                else
                    $value = (int) $value;
            }
            elseif (in_array(strtolower($value, ['true', 'false'])))
            {
                $value = strtolower($value) === 'true' ? true : false;
            }
        }

        $c = root\config::select($package);
        $c->set($key, $value);
        ui::nl();

        if ($c->save())
            ui::success('OK', $key.' => '.$original_value);
        else
            ui::error('FAILED', $key.' => '.$original_value);
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
            ui::nl();
            ui::line("Available options for `{$package}`:");
            $options = root\config::select($package)->as_array();

            if (empty($options))
            {
                ui::line('No options available.');
            }
            else
            {
                ui::nl();
                ui::al($options);
            }
        }
        else
        {
            ui::nl();
            ui::line('Available packages:');

            $list = root\config::get_list();

            if (!empty($list))
            {
                ui::ul($list);
                ui::nl();
                ui::line(
                    'Use `./dot config vendor.package` to see all options '.
                    'for particular package.'
                );
            }
            else
            {
                ui::nl();
                ui::line('No configuration available.');
            }
        }
    }
}
