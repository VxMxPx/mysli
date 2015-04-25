<?php

namespace mysli\util\config\sh\config;

__use(__namespace__, '
    ./config
    mysli.framework.fs
    mysli.framework.type/arr
    mysli.framework.cli/param,output -> cparam,cout
');

/**
 * CLI front-end.
 * @param  array $arguments
 * @return null
 */
function __init(array $args)
{
    $params = new cparam('Mysli Config', $args);
    $params->command = 'config';
    $params->add('--list/-l', [
        'type' => 'bool',
        'help' => 'List all packages with configuration values or all '.
                  'configuration options for particular package.'
    ]);
    $params->add('--package/-p', [
        'type' => 'str',
        'help' => 'Get all values, for package when in combination '.
                  'with --list; set package when --set.'
    ]);
    $params->add('--set/-s', [
        'type'  => 'arr',
        'min'   => 2,
        'max'   => 2,
        'help'  => 'Key value to be set. For example: -s key value'
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

    if ($values['list'])
    {
        get_list($values['package']);
    }
    elseif (is_array($values['set']))
    {
        set_value(
            $values['package'],
            $values['set'][0],
            $values['set'][1],
            $values['string']
        );
    }
    else
    {
        cout::line($params->help());
    }
}
/**
 * Set value for package
 * @param string  $package
 * @param string  $key
 * @param string  $value
 * @param boolean $string
 */
function set_value($package, $key, $value, $string)
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

    $c = config::select($package);
    $c->set($key, $value);
    cout::nl();

    if ($c->save())
    {
        cout::format("+green OK:-all  {$key} => {$original_value}");
    }
    else
    {
        cout::format("+red FAILED:-all  {$key} => {$original_value}");
    }
}
/**
 * Get list of all packages with config
 * or config values for particular package
 * @param  string $package
 */
function get_list($package)
{
    if ($package)
    {
        cout::nl();
        cout::line("Available options for `{$package}`:");
        $options = config::select($package)->as_array();

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
            'Use `./dot config -l -p vendor/package` to see all options '.
            'for particular package.'
        );
    }
}
