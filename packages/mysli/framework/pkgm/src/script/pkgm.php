<?php

namespace mysli\framework\pkgm\script;

__use(__namespace__, '
    ./pkgm as root/pkgm
    mysli/framework/cli/{param,output,input} AS {param,cout,cin}
    mysli/framework/type/arr
    mysli/framework/fs
');

class pkgm {

    /**
     * Inital call
     * @param  array $args
     */
    static function run($args) {
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
        if (!$param->is_valid()) {
            cout::line($param->messages());
            return;
        }

        $val = $param->values();

        if ($val['enable']) {
            self::enable($val['package'], $val['rec'], $val['dev']);
        } elseif ($val['disable']) {
            self::disable($val['package']);
        } elseif ($val['repair']) {
            self::repair();
        } elseif ($val['list']) {
            self::do_list($val['list']);
        } elseif ($val['meta']) {
            self::meta($val['package']);
        } else {
            cout::warn(
                'Invalid command, use --help to see available commands.');
        }
    }

    // Enable

    /**
     * Enable particular package, and dependencies.
     * @param string $pkg
     * @param boolean $rec include recommended packages
     * @param boolean $dev include development packages
     */
    static function enable($pkg, $rec=false, $dev=false) {
        if (root\pkgm::is_enabled($pkg)) {
            cout::warn("Package is already enabled: {$pkg}");
            return false;
        }
        if (!root\pkgm::exists($pkg)) {
            cout::warn("Package not found: {$pkg}");
            return false;
        }

        // Regular dependencies
        $dependencies     = root\pkgm::list_dependencies($pkg, true);
        // Recommended
        $rec_dependencies = root\pkgm::list_dependencies($pkg, true, 'recommend');
        // Development
        $dev_dependencies = root\pkgm::list_dependencies($pkg, true, 'dev');

        if ($rec) {
            if ($rec && !empty($rec_dependencies['missing'])) {
                cout::line('Following recommended dependencies are missing: ');
                cout::line(arr::readable($rec_dependencies['missing'], 2));
            }
            $dependencies = array_merge(
                $dependencies['disabled'], $rec_dependencies['disabled']);
        }
        if ($dev) {
            if ($rec && !empty($dev_dependencies['missing'])) {
                cout::line('Following development dependencies are missing: ');
                cout::line(arr::readable($dev_dependencies['missing'], 2));
            }
            $dependencies = array_merge(
                $dependencies['disabled'], $dev_dependencies['disabled']);
        }

        if (!empty($dependencies['missing'])) {
            cout::format(
                "+red Cannot enable, following packages/extensions ".
                "are missing:\n\n%s\n",
                [arr::readable($dependencies['missing'], 2)]);
            return false;
        }

        if (count($dependencies['disabled'])) {
            cout::line(
                "\nPackage `{$pkg}` require:\n" .
                arr::readable($dependencies['disabled'], 4) . "\n");

            if (!cin::confirm("Continue and enable required packages?")) {
                cout::line('Terminated.');
                return false;
            }

            foreach ($dependencies['disabled'] as $dependency => $version) {
                if (!self::enable_helper($dependency, $pkg)) {
                    return false;
                }
            }
        }

        self::enable_helper($pkg, 'installer');

        // Print recommendations...
        if (!$rec && !empty($rec_dependencies['disabled'])) {
            cout::line('Recommended dependencies: ');
            cout::line(arr::readable($rec_dependencies['disabled'], 2));
        }
        // Print recommendations...
        if (!$dev && !empty($dev_dependencies['disabled'])) {
            cout::line('Development dependencies: ');
            cout::line(arr::readable($dev_dependencies['disabled'], 2));
        }
    }
    private static function enable_helper($package, $by) {

        cout::line("Enabling... {$package}");

        if (self::run_setup($package, 'enable')) {

            if (root\pkgm::enable($package, $by)) {
                cout::success("Enabled: {$package}");
                return true;
            } else {
                cout::error("Failed: {$package}");
                return false;
            }

        } else {
            cout::error("Setup failed for: {$package}");
            return false;
        }
    }

    // Disable

    /**
     * Disable particular package, and sub-packages.
     * @param string $pkg
     */
    static function disable($pkg) {
        // Can't disable something that isn't enabled
        if (!root\pkgm::is_enabled($pkg)) {
            cout::warn("Package not enabled: `{$pkg}`.");
            return false;
        }

        // Get package dependees!
        $dependees = root\pkgm::list_dependees($pkg, true);
        array_pop($dependees); // remove self

        // If we have dependees, then disable them all first!
        if (!empty($dependees)) {
            cout::line(
                "Package `{$pkg}` is required by:\n" .
                arr::readable($dependees, 4) . "\n");

            if (!cin::confirm('Disable listed packages?')) {
                cout::plain('Terminated.');
                return false;
            }

            foreach ($dependees as $dependee) {
                if (!self::disable_helper($dependee)) {
                    return false;
                }
            }
        }

        // Finally, disable the actual package
        return self::disable_helper($pkg);
    }
    private static function disable_helper($package) {

        cout::line("Disabling... {$package}");

        if (self::run_setup($package, 'disable')) {

            if (root\pkgm::disable($package)) {
                cout::success("Disabled: {$package}");
                return true;
            } else {
                cout::error("Failed to disable: {$package}");
                return false;
            }

        } else {
            cout::error("Setup failed for: {$package}");
            return false;
        }
    }

    // Repair

    /**
     * Check for disabled/missing packages which are needed, and enable them.
     */
    static function repair() {
        cout::line('Will scan database for missing dependencies....');
        foreach (root\pkgm::list_enabled() as $package) {
            cout::line("Found: `{$package}`", false);
            $dependencies = root\pkgm::list_dependencies($package);
            if (empty($dependencies['disabled'])
                && empty($dependencies['missing'])) {
                cout::format('+right +green Nothing to do');
            }
            if (!empty($dependencies['disabled'])) {
                cout::nl();
                foreach ($dependencies['disabled'] as $ddep => $vel) {
                    if (!root\pkgm::is_enabled($ddep)) {
                        self::enable($ddep);
                    }
                }
            }
            if (!empty($dependencies['missing'])) {
                cout::nl();
                cout::format(
                    "+redMissing packages:\n%s\n",
                    arr::readable($dependencies['missing'], 2));
            }
        }
    }

    // List

    /**
     * List packages.
     * @param  string $option all|enabled|disabled
     */
    static function do_list($option) {
        switch ($option) {
            case 'enabled':
                cout::line(arr::readable(root\pkgm::list_enabled(), 2));
                break;

            case 'disabled':
                cout::line(arr::readable(root\pkgm::list_disabled(), 2));
                break;

            case 'all':
                cout::line('Enabled packages: ');
                self::do_list('enabled');
                cout::nl();
                cout::line('Disabled packages: ');
                self::do_list('disabled');
                break;

            default:
                cout::line('Invalid value.');
                break;
        }
    }

    // Meta

    /**
     * Get meta for particular package.
     * @param  string $package
     */
    static function meta($package) {
        if (!root\pkgm::exists($package)) {
            cout::warn('No such package: `'.$package.'`');
            return;
        }
        cout::line(arr::readable(root\pkgm::meta($package)));
    }

    // CSI Handling

    /**
     * CSI Input.
     * @param  array $properties
     * @return mixed
     */
    private static function csi_input(array $properties) {
        // If type not in array, quit right away!
        if (!in_array(
                $properties['type'],
                ['input', 'password', 'textarea', 'radio', 'checkbox'])) {
            return;
        }

        $question = '';
        if ($properties['label']) {
            $question .= $properties['label'];
        }

        // Add default if exists
        if ($properties['default']) {
            if (!empty($properties['options'])) {
                $default = $properties['options'][$properties['default']];
            } else {
                $default = $properties['default'];
            }
            $question .= ' [' . $default . ']';
        }

        // Print question
        cout::line($question);

        switch ($properties['type']) {
            case 'input':
                return cin::line(
                    '> ', function ($input) { return $input; });
                break;

            case 'password':
                return cin::password(
                    '> ', function ($input) { return $input; });
                break;

            case 'textarea':
                return cin::multiline(
                    '> ', function ($input) { return $input; });
                break;

            case 'radio':
                $options = $properties['options'];
                $keys = array_keys($options);
                $element = 0;
                cout::line(arr::readable(array_values($options)));
                cout::line('Enter one of the numbers (e.g., 1).');

                return cout::input(
                    '> ', function ($input) use ($options, $keys) {
                        if (!isset($keys[$input])) {
                            return null;
                        } else {
                            return $keys[$input];
                        }});
                break;

            case 'checkbox':
                $options = $properties['options'];
                $keys = array_keys($options);
                $element = 0;
                cout::line(arr::readable(array_values($options)));
                cout::line('Enter one or more numbers (e.g., 1, 2, 3).');

                return cin::line(
                    '> ', function ($input) use ($options, $keys) {
                        $input = \Core\Str::explode_trim(',', $input);
                        $real = [];
                        foreach ($input as $val) {
                            if (!isset($keys[$val])) {
                                return null;
                            } else {
                                $real[] = $keys[$val];
                            }
                        }
                        return $real;
                        });
                break;
        }
    }
    /**
     * Handle ~csi response.
     * @param object $csi
     */
    private static function csi_process($csi) {
        do {
            switch ($csi->status()) {
                // One of the fields interrupted the process.
                case 'interrupted':
                    $fields = [];
                    foreach ($csi->get_fields() as $fid => $properties) {
                        if (!isset($properties['status']) === null) {
                            $fields[$fid] = $properties;
                        }
                    }
                    break;

                // Validation failed.
                case 'failed':
                    $fields = [];
                    cout::line('Please correct following errors:');
                    foreach ($csi->get_fields() as $fid => $properties) {
                        if ($properties['messages']) {
                            if (!is_array($properties['messages'])) {
                                $properties['messages'] =
                                    [$properties['messages']];
                            }
                            cout::warn(
                                implode("\n", $properties['messages']));
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
            if (empty($fields)) {
                return true;
            }

            // Run through fields, and output them!
            foreach ($fields as $fid => $properties) {
                if ($properties['type'] === 'hidden') {
                    continue;
                }

                if ($properties['type'] === 'paragraph') {
                    cout::line($properties['label']);
                }

                do {
                    $value = self::csi_input($properties);
                    $properties['value'] =
                        ($value === '' && $properties['default'])
                            ? $properties['default']
                            : $value;
                    // Validate individual field (if it has callback)
                    if ($properties['callback']) {
                        $status = call_user_func_array(
                            $properties['callback'], [&$properties]);
                        if (!$status) {
                            if (isset($properties['messages'])) {
                                $properties['messages'] =
                                    is_array($properties['messages'])
                                        ? $properties['messages']
                                        : [$properties['messages']];
                                cout::warn(
                                    implode("\n", $properties['messages']));
                            }
                        }
                    } else {
                        $status = true;
                    }
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
    private static function run_setup($pkg, $action) {

        $setup_file = fs::pkgpath($pkg, 'src/setup.php');
        $setup_fnc  = str_replace('/', '\\', $pkg) . '\\setup\\' . $action;

        if (!function_exists($setup_fnc)) {
            if (!file_exists($setup_file)) {
                return true;
            } else {
                include($setup_file);
            }
        }

        if (!function_exists($setup_fnc)) {
            return true;
        }

        return (self::call_setup_function($setup_fnc) !== false);
    }
    /**
     * Execute setup step, and handle `csi`.
     * @param  string $class
     * @param  string $method
     * @param  array  $values
     * @return boolean
     */
    private static function call_setup_function($call, array $values=null) {
        if (!function_exists($call)) {
            return true;
        }

        $csi = null;

        do {
            $csi = call_user_func($call, $csi);
            if (is_object($csi)) {
                $class = get_class($csi);
                if (substr($class, -4) === '\\csi') {
                    if ($values) {
                        $csi->set_multiple($values);
                    }
                    $csi_result = self::csi_process($csi);
                    continue;
                }
            }
            $csi_result = $csi;
        } while(is_object($csi));

        return $csi_result;
    }
}
