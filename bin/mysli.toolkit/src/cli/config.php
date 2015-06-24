<?php

namespace mysli\util\config\sh;

__use(__namespace__, '
    ./config -> root\config
    mysli.framework.fs
    mysli.framework.type/arr
    mysli.framework.cli/param,output -> cparam,cout
');

class config
{
    /**
     * CLI front-end.
     * @param  array $arguments
     * @return null
     */
    static function __init(array $args)
    {
        $params = new cparam('Mysli Config', $args);
        $params->command = 'config';

        $params->add('PACKAGE', [
            'type' => 'str',
            'required' => false,
            'help' => 'Package which will be affected. '.
                      'If no KEY then list all values.'
        ]);
        $params->add('KEY', [
            'type' => 'str',
            'required' => false,
            'help' => 'Configuration key which to get/set. ',
                      'If no VALUE, then show current value.'
        ]);
        $params->add('VALUE', [
            'type' => 'str',
            'required' => false,
            'help' => 'Configuration value which will be set.'
        ]);
        $params->add('--string', [
            'type' => 'bool',
            'help' => 'Force value to be string when setting.'
        ]);

        $params->parse();

        if (!$params->is_valid())
        {
            cout::line($params->messages());
            return;
        }

        $values = $params->values();

        if (!$values['package'])
        {
            self::get_list(false);
        }
        else
        {
            if (!$values['key'])
            {
                self::get_list($values['package']);
            }
            else
            {
                if (!$values['value'])
                {
                    self::get_value($values['package'], $values['key']);
                }
                else
                {
                    self::set_value(
                        $values['package'],
                        $values['key'],
                        $values['value'],
                        $values['string']
                    );
                }
            }
        }
    }
    /**
     * Get value for package
     * @param string $package
     * @param string $key
     */
    static function get_value($package, $key)
    {
        $values = root\config::select($package, $key);
        $values = [$key => $values];
        cout::nl();
        cout::line(arr::readable($values, 2, 2, ' : ', "\n", true));
    }
    /**
     * Set value for package
     * @param string  $package
     * @param string  $key
     * @param string  $value
     * @param boolean $string
     */
    static function set_value($package, $key, $value, $string)
    {
        if (!\core\pkg::is_enabled($package))
        {
            cout::error("Package is not enabled: `{$package}`");
            return;
        }

        $original_value = $value;

        if (!$string)
        {
            if (is_numeric($value))
            {
                if (strpos($value, '.'))
                {
                    $value = (float) $value;
                }
                else
                {
                    $value = (int) $value;
                }
            }
            elseif ($value === 'true' || $value === 'false')
            {
                $value = $value === 'true' ? true : false;
            }
        }

        $c = root\config::select($package);
        $c->set($key, $value);
        cout::nl();

        if ($c->save())
        {
            cout::format("<green>OK:</green> {$key} => {$original_value}\n");
        }
        else
        {
            cout::format("<red>FAILED</red> {$key} => {$original_value}\n");
        }
    }
    /**
     * Get list of all packages with config
     * or config values for particular package
     * @param  string $package
     */
    static function get_list($package)
    {
        if ($package)
        {
            cout::nl();
            cout::line("Available options for `{$package}`:");
            $options = root\config::select($package)->as_array();

            if (empty($options))
            {
                cout::line('No options available.');
            }
            else
            {
                cout::nl();
                cout::line(arr::readable($options, 2, 2, ' : ', "\n", true));
            }
        }
        else
        {
            $files = fs::ls(fs::datpath('mysli/util/config'));
            cout::nl();
            cout::line('Available packages:');

            foreach ($files as $file)
            {
                cout::line('  '.substr($file, 0, -5));
            }

            cout::nl();
            cout::line(
                'Use `./dot config vendor.package` to see all options '.
                'for particular package.'
            );
        }
    }
}
