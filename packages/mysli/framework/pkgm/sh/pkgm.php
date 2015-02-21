<?php

namespace mysli\framework\pkgm\sh\pkgm;

__use(__namespace__, '
    ./pkgm
    mysli.framework.cli/param,output,input  AS  param,cout,cin
    mysli.framework.type/arr,str
    mysli.framework.fs
');

/**
 * Inital call
 * @param  array $args
 */
function __init(array $args)
{
    $param = new param('Mysli Pkgm', $args);
    $param->command = 'pkgm';
    $param->description = 'Manage Mysli Packages.';
    $param->add('-e/--enable', [
        'help' => 'Enable a package',
        'type' => 'bool'
    ]);
    $param->add('--rec', [
        'help' => 'Enable recommended packages for particular package.',
        'type' => 'bool'
    ]);
    $param->add('--dev', [
        'help' => 'Enable development packages for particular package.',
        'type' => 'bool'
    ]);
    $param->add('-d/--disable', [
        'exclude' => ['enable', 'rec', 'dev'],
        'help'    => 'Disable a package',
        'type'    => 'bool'
    ]);
    $param->add('--repair', [
        'help'   => 'Scan and repair (if needed) packages database.',
        'type'   => 'bool'
    ]);
    $param->add('--list', [
        'exclude' => ['enable', 'disable', 'rec', 'dev'],
        'help' => 'Display a list of packages. Available options: '.
                  'all, enabled, disabled.',
        'type' => 'str'
    ]);
    $param->add('--meta', [
        'exclude' => ['enable', 'disable', 'list', 'rec', 'dev'],
        'help'    => 'Display meta information for particular package.',
        'type'    => 'bool'
    ]);
    $param->add('PACKAGE', [
        'help'     => 'Package to preform action on.',
        'type'     => 'str',
        'required' => false
    ]);

    $param->parse();

    if (!$param->is_valid())
    {
        cout::line($param->messages());
        return;
    }

    $val = $param->values();

    if ($val['enable'])
        enable($val['package'], $val['rec'], $val['dev']);
    elseif ($val['disable'])
        disable($val['package']);
    elseif ($val['repair'])
        repair();
    elseif ($val['list'])
        do_list($val['list']);
    elseif ($val['meta'])
        meta($val['package']);
    else
        cout::warn('Invalid command, use --help to see available commands.');

}

// Enable

/**
 * Enable particular package, and dependencies.
 * @param string  $pkg
 * @param boolean $rec include recommended packages
 * @param boolean $dev include development packages
 */
function enable($pkg, $rec=false, $dev=false)
{
    if (pkgm::is_enabled($pkg))
    {
        cout::warn("[!] Package is already enabled: {$pkg}");
        return false;
    }

    if (!pkgm::exists($pkg))
    {
        cout::warn("[!] Package not found: {$pkg}");
        return false;
    }

    // Regular dependencies
    $dependencies     = pkgm::list_dependencies($pkg, true);
    // Recommended
    $rec_dependencies = pkgm::list_dependencies($pkg, true, 'recommend');
    // Development
    $dev_dependencies = pkgm::list_dependencies($pkg, true, 'dev');

    if ($rec)
    {
        if ($rec && !empty($rec_dependencies['missing']))
        {
            cout::line("\n* Following recommended dependencies are missing:");
            cout::line(arr::readable($rec_dependencies['missing'], 4));
        }

        $dependencies = array_merge(
            $dependencies['disabled'],
            $rec_dependencies['disabled']
        );
    }

    if ($dev)
    {
        if ($rec && !empty($dev_dependencies['missing']))
        {
            cout::line("\n* Following development dependencies are missing:");
            cout::line(arr::readable($dev_dependencies['missing'], 4));
        }

        $dependencies = array_merge(
            $dependencies['disabled'],
            $dev_dependencies['disabled']
        );
    }

    if (!empty($dependencies['missing']))
    {
        cout::format(
            "+red [!] Cannot enable, ".
            "following packages/extensions are missing:\n\n%s\n",
            [arr::readable($dependencies['missing'], 4)]
        );

        return false;
    }

    if (count($dependencies['disabled']))
    {
        cout::line(
            "\n* Package `{$pkg}` require:\n" .
            arr::readable($dependencies['disabled'], 4)
        );

        if (!cin::confirm("[?] Continue and enable required packages?"))
        {
            cout::line('Terminated.');
            return false;
        }

        foreach ($dependencies['disabled'] as $dependency => $version)
            if (!enable_helper($dependency, $pkg))
                return false;
    }

    enable_helper($pkg, 'installer');

    // Print recommendations...
    if (!$rec && !empty($rec_dependencies['disabled']))
    {
        cout::line("\n* Recommended dependencies: ");
        cout::line(arr::readable($rec_dependencies['disabled'], 4));
    }
    // Print recommendations...
    if (!$dev && !empty($dev_dependencies['disabled']))
    {
        cout::line("\n* Development dependencies: ");
        cout::line(arr::readable($dev_dependencies['disabled'], 4));
    }
}
function enable_helper($package, $by)
{
    cout::line("Package `{$package}`", false);

    if (run_setup($package, 'enable'))
    {
        if (pkgm::enable($package, $by))
        {
            cout::format("+green+right ENABLED");
            return true;
        }
        else
        {
            cout::format("+red+right FAILED TO ENABLED");
            return false;
        }
    }
    else
    {
        cout::error("[!] Setup failed for: `{$package}`");
        return false;
    }
}

// Disable

/**
 * Disable particular package, and sub-packages.
 * @param string $pkg
 */
function disable($pkg)
{
    // Can't disable something that isn't enabled
    if (!pkgm::is_enabled($pkg))
    {
        cout::warn("[!] Package not enabled: `{$pkg}`.");
        return false;
    }

    // Get package dependees!
    $dependees = pkgm::list_dependees($pkg, true);
    array_pop($dependees); // remove self

    // If we have dependees, then disable them all first!
    if (!empty($dependees))
    {
        cout::line(
            "\n* Package `{$pkg}` is required by:\n" .
            arr::readable($dependees, 4)
        );

        if (!cin::confirm('[?] Disable listed packages?'))
        {
            cout::plain('Terminated.');
            return false;
        }

        foreach ($dependees as $dependee)
            if (!disable_helper($dependee))
                return false;
    }

    // Finally, disable the actual package
    return disable_helper($pkg);
}
function disable_helper($package)
{
    cout::line("Package `{$package}`", false);

    if (run_setup($package, 'disable'))
    {
        if (pkgm::disable($package))
        {
            cout::format("+green+right DISABLED");
            return true;
        }
        else
        {
            cout::format("+red+right FAILED TO DISABLE");
            return false;
        }
    }
    else
    {
        cout::error("[!] Setup failed for: `{$package}`");
        return false;
    }
}

// Repair

/**
 * Check for disabled/missing packages which are needed, and enable them.
 */
function repair()
{
    cout::line("\n* Scanning database for missing dependencies...");

    foreach (pkgm::list_enabled() as $package)
    {
        cout::line("    Found `{$package}`", false);
        $dependencies = pkgm::list_dependencies($package, true);

        if (empty($dependencies['disabled']) &&
            empty($dependencies['missing']))
        {
            cout::format('+right+green OK');
        }

        if (!empty($dependencies['missing']))
        {
            cout::format('+right+red FAILED');
            cout::format(
                "+red [!] Missing packages:\n%s\n",
                [arr::readable($dependencies['missing'], 4)]
            );
        }

        if (!empty($dependencies['disabled']))
        {
            cout::format('+right+green ...');
            foreach ($dependencies['disabled'] as $ddep => $vel)
            {
                if (!pkgm::is_enabled($ddep))
                {
                    cout::line('        ', false);
                    enable_helper($ddep, $package);
                }
            }
        }
    }
}

// List

/**
 * List packages.
 * @param  string $option all|enabled|disabled
 */
function do_list($option)
{
    switch ($option) {
        case 'enabled':
            cout::line(arr::readable(pkgm::list_enabled(), 4));
            break;

        case 'disabled':
            cout::line(arr::readable(pkgm::list_disabled(), 4));
            break;

        case 'all':
            cout::line("\n* Enabled packages:");
            do_list('enabled');
            cout::nl();
            cout::line("\n* Disabled packages:");
            do_list('disabled');
            break;

        default:
            cout::line('[!] Invalid value.');
            break;
    }
}

// Meta

/**
 * Get meta for particular package.
 * @param  string $package
 */
function meta($package)
{
    if (!pkgm::exists($package))
        cout::warn('[!] No such package: `'.$package.'`');
    else
        cout::line(arr::readable(pkgm::meta($package)));
}

// CSI Handling

/**
 * CSI Input.
 * @param  array $properties
 * @return mixed
 */
function csi_input(array $properties)
{
    cout::nl();

    // If type not in array, quit right away!
    if (!in_array($properties['type'],
        ['input', 'password', 'textarea', 'radio', 'checkbox']))
    {
        return;
    }

    $question = '';
    if ($properties['label'])
        $question .= $properties['label'];

    // Add default if exists
    if ($properties['default'])
    {
        if (!empty($properties['options']))
            $default = $properties['options'][$properties['default']];
        else
            $default = $properties['default'];

        $question .= ' [' . $default . ']';
    }

    // Print question
    cout::line('[?] '.$question);

    switch ($properties['type'])
    {
        case 'input':
            return cin::line('> ', function ($input) {
                return $input;
            });

        case 'password':
            return cin::password('> ', function ($input) {
                return $input;
            });

        case 'textarea':
            return cin::multiline('> ', function ($input) {
                return $input;
            });

        case 'radio':
            $options = $properties['options'];
            $keys = array_keys($options);
            $element = 0;
            cout::line(arr::readable(array_values($options)));
            cout::line('Enter one of the numbers (e.g., 1).');
            return cout::input('> ', function ($input) use ($options, $keys)
            {
                if (!isset($keys[$input]))
                    return null;
                else
                    return $keys[$input];
            });

        case 'checkbox':
            $options = $properties['options'];
            $keys = array_keys($options);
            $element = 0;
            cout::line(arr::readable(array_values($options)));
            cout::line('Enter one or more numbers (e.g., 1, 2, 3).');
            return cin::line('> ', function ($input) use ($options, $keys)
            {
                $input = str::explode_trim(',', $input);
                $real = [];

                foreach ($input as $val)
                {
                    if (!isset($keys[$val]))
                        return null;
                    else
                        $real[] = $keys[$val];
                }

                return $real;
            });
    }
}
/**
 * Handle ~csi response.
 * @param object $csi
 */
function csi_process($csi)
{
    do {
        switch ($csi->status())
        {
            // One of the fields interrupted the process.
            case 'interrupted':
                $fields = [];

                foreach ($csi->get_fields() as $fid => $properties)
                    if (!isset($properties['status']) === null)
                        $fields[$fid] = $properties;

                break;

            // Validation failed.
            case 'failed':
                $fields = [];
                cout::line('[!] Please correct following errors:');

                foreach ($csi->get_fields() as $fid => $properties)
                {
                    if ($properties['messages'])
                    {
                        if (!is_array($properties['messages']))
                            $properties['messages'] = [$properties['messages']];

                        cout::warn(implode("\n", $properties['messages']));
                        $fields[$fid] = $properties;
                    }
                }
                break;

            // Currently there's no data
            case 'none':
                $fields = $csi->get_fields();
                break;
        }

        // No fields, nothing to do!
        if (empty($fields))
            return true;

        // Run through fields, and output them!
        foreach ($fields as $fid => $properties)
        {
            if ($properties['type'] === 'hidden')
                continue;

            if ($properties['type'] === 'paragraph')
                cout::line($properties['label']);

            do {
                $value = csi_input($properties);

                $properties['value'] = ($value === '' && $properties['default']) ?
                    $properties['default'] :
                    $value;

                // Validate individual field (if it has callback)
                if ($properties['callback'])
                {
                    $status = call_user_func_array(
                        $properties['callback'],
                        [&$properties]
                    );

                    if (!$status)
                    {
                        if (isset($properties['messages']))
                        {
                            $properties['messages'] =
                                is_array($properties['messages']) ?
                                    $properties['messages'] :
                                    [$properties['messages']];

                            cout::warn(implode("\n", $properties['messages']));
                        }
                    }
                }
                else
                    $status = true;

                // Set either value from the input
                // or the default if exists.
                $csi->set($fid, $properties['value']);

            } while (!$status);
        }
    // Until validation succeed!
    } while (!$csi->validate());

    return $csi->status() === 'success';
}

// Setup execution

/**
 * Call setup for enabling/disabling of package.
 * @param  string $pkg
 * @param  string $action enable/disable
 * @return boolean
 */
function run_setup($pkg, $action)
{
    $setup_file = fs::pkgpath($pkg, 'src/setup.php');
    $setup_fnc  = str_replace('/', '\\', $pkg) . '\\setup\\' . $action;

    if (!function_exists($setup_fnc))
    {
        if (!file_exists($setup_file))
            return true;
        else
            include($setup_file);
    }

    if (!function_exists($setup_fnc))
        return true;

    return (call_setup_function($setup_fnc) !== false);
}
/**
 * Execute setup step, and handle `csi`.
 * @param  string $class
 * @param  string $method
 * @param  array  $values
 * @return boolean
 */
function call_setup_function($call, array $values=null)
{
    if (!function_exists($call))
        return true;

    $csi = null;

    do {
        $csi = call_user_func($call, $csi);

        if (is_object($csi))
        {
            $class = get_class($csi);
            if (substr($class, -4) === '\\csi')
            {
                if ($values)
                    $csi->set_multiple($values);

                $csi_result = csi_process($csi);
                continue;
            }
        }

        $csi_result = $csi;

    } while(is_object($csi));

    return $csi_result;
}
