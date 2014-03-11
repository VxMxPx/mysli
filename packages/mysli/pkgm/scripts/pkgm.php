<?php

namespace Mysli\Pkgm\Script;

class Pkgm
{
    protected $pkgm;
    protected $on_disable_remove_data = false;
    protected $on_disable_remove_config = false;

    public function __construct(\Mysli\Pkgm $pkgm)
    {
        $this->pkgm = $pkgm;
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management',
            'packages <OPTION> [ARGUMENTS...]',
            [
                'enable'  => 'Will enable particular package.',
                'disable' => 'Will disable particular package.',
                'list'    => 'Will list enabled / disabled / obsolete packages.'
            ]
        );

        return true;
    }

    /**
     * CSI Input.
     * --
     * @param  array $properties
     * --
     * @return mixed
     */
    protected function csi_input(array $properties)
    {
        // If type not in array, quit right away! :)
        $allowed = ['input', 'password', 'textarea', 'radio', 'checkbox'];
        if ( ! in_array($properties['type'], $allowed) ) {
            return;
        }

        // Formulate a question.
        $question = '';
        if ($properties['label'])   $question .= $properties['label'];

        if ($properties['default']) {
            if (!empty($properties['options'])) {
                $default = $properties['options'][$properties['default']];
            } else {
                $default = $properties['default'];
            }
            $question .= ' [' . $default . ']';
        }

        \Cli\Util::nl();
        \Cli\Util::plain($question);

        switch ($properties['type']) {
            case 'input':
                return \Cli\Util::input('> ', function ($input) { return $input; });
                break;

            case 'password':
                return \Cli\Util::password('> ', function ($input) { return $input; });
                break;

            case 'textarea':
                return \Cli\Util::input_multiline('> ', function ($input) { return $input; });
                break;

            case 'radio':
                $options = $properties['options'];
                $keys = array_keys($options);
                $element = 0;
                \Cli\Util::plain(\Core\Arr::readable(array_values($options)));
                \Cli\Util::plain('Enter one of the numbers (e.g., 1).');
                return \Cli\Util::input('> ', function ($input) use ($options, $keys) {
                    if (!isset($keys[$input])) {
                        return null;
                    } else {
                        return $keys[$input];
                    }
                });
                break;

            case 'checkbox':
                $options = $properties['options'];
                $keys = array_keys($options);
                $element = 0;
                \Cli\Util::plain(\Core\Arr::readable(array_values($options)));
                \Cli\Util::plain('Enter one or more numbers (e.g., 1, 2, 3).');
                return \Cli\Util::input('> ', function ($input) use ($options, $keys) {
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
     * --
     * @param object $csi ~csi
     */
    protected function csi_process($csi)
    {
        do {
            switch ($csi->status()) {
                // One of the fields interrupted the process.
                case 'interrupted':
                    $fields = [];
                    foreach ($csi->get_fields() as $field_id => $properties) {
                        if (!isset($properties['status']) === null) {
                            $fields[$field_id] = $properties;
                        }
                    }
                    break;

                // Validation failed.
                case 'failed':
                    $fields = [];
                    foreach ($csi->get_fields() as $field_id => $properties) {
                        if (isset($properties['messages'])) {
                            \Cli\Util::warn(implode("\n", $properties['messages']));
                            $fields[$field_id] = $properties;
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
            foreach ($fields as $field_id => $properties) {

                if ($properties['type'] === 'hidden') { continue; }

                $value = $this->csi_input($properties);
                // Set either value from the input or the default if exists.
                $value = $value === '' && $properties['default']
                    ? $properties['default']
                    : $value;
                $csi->set($field_id, $value);
            }
        // Until validation succeed!
        } while (!$csi->validate());

        return $csi->status() === 'success';
    }

    /**
     * Will execute particular setup step.
     * --
     * @param  object  $setup package/setup
     * @param  string  $step
     * @param  array   $values any values to set to the $csi (if exists!)
     * --
     * @return boolean null if method not found!
     */
    protected function execute_setup_step($setup, $step, array $values = null)
    {
        if (method_exists($setup, $step)) {
            do {
                $csi = $setup->{$step}();
                if ($this->pkgm->obj_to_role($csi) === 'csi') {
                    if ($values) {
                        $csi->set_multiple($values);
                    }
                    $csi_result = $this->csi_process($csi);
                } else {
                    $csi_result = $csi;
                }
            } while(is_object($csi));

            return $csi_result;
        }
    }

    /**
     * Print help for package enabling.
     */
    public function help_enable()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management :: ENABLE',
            'packages enable <PACKAGE NAME>'
        );
        \Cli\Util::plain('Example: packages enable mysli/config');

        return true;
    }

    /**
     * Enable helper.
     * --
     * @param  string $pkg
     * @param  string $enabled_by By which package this package was enabled.
     *                            Null if it was enable by user.
     * --
     * @return boolean
     */
    protected function enable_helper($pkg, $enabled_by = null)
    {
        // Get setup object and execute before_enable step!
        $setup = $this->pkgm->construct_setup($pkg);
        if ( $this->execute_setup_step($setup, 'before_enable') === false ) {
            \Cli\Util::error('Setup failed for: ' . $pkg);
            return false;
        }

        // Print help...
        \Cli\Util::nl();
        \Cli\Util::plain('Enabling ' . $pkg . '...');
        \Cli\Util::plain(str_repeat('-', 12 + strlen($pkg)));

        // If not enabled successfully return false!
        if (!$this->pkgm->enable($pkg, $enabled_by)) {
            \Cli\Util::error('Failed to enable: ' . $pkg);
            return false;
        }

        // If not successful, print warning, but don't terminate the process
        if ( $this->execute_setup_step($setup, 'after_enable') === false ) {
            \Cli\Util::warn('Problems with: ' . $pkg);
        }

        // Final conformation
        \Cli\Util::success('Enabled: ' . $pkg);
        return true;
    }

    /**
     * Enable particular package, and dependencies.
     * --
     * @param  string $pkg
     */
    public function action_enable($pkg)
    {
        if (!$pkg) {
            return $this->help_enable();
        }

        if ($this->pkgm->is_enabled($pkg)) {
            \Cli\Util::warn("Package is already enabled: `{$pkg}`.");
            return false;
        }

        if (!$this->pkgm->resolve($pkg, 'disabled')) {
            \Cli\Util::warn("Package not found: `{$pkg}`.");
            return false;
        }

        $dependencies = $this->pkgm->get_dependencies($pkg, true);

        if (!empty($dependencies['missing'])) {
            \Cli\Util::warn(
                "Cannot enable, following packages are missing: \n\n" .
                \Core\Arr::readable($dependencies['missing'], 2) . "\n"
            );
            return false;
        }

        if (count($dependencies['disabled'])) {
            \Cli\Util::plain(
                "The following packages needs to be enabled: \n\n" .
                \Core\Arr::readable($dependencies['disabled'], 2) . "\n"
            );

            if (!\Cli\Util::confirm('Continue and enable required packages?')) {
                \Cli\Util::plain('Process terminated.');
                return false;
            }

            foreach ($dependencies['disabled'] as $dependency => $version) {
                if (!$this->enable_helper($dependency, $pkg)) {
                    return false;
                }
            }
        }

        return $this->enable_helper($pkg);
    }

    /**
     * Print help for package disabling.
     */
    public function help_disable()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management :: DISABLE',
            'packages disable <PACKAGE NAME>'
        );
        \Cli\Util::plain('Example: packages disable mysli/config');

        return true;
    }

    /**
     * Helper for package disabling.
     * --
     * @param  string $pkg
     * --
     * @return boolean
     */
    protected function disable_helper($pkg)
    {
        // Print help...
        \Cli\Util::nl();
        \Cli\Util::plain('Disabling ' . $pkg . '...');
        \Cli\Util::plain(str_repeat('-', 12 + strlen($pkg)));

        // Get setup object, and execute before disable
        $setup = $this->pkgm->construct_setup($pkg);
        $result = $this->execute_setup_step(
            $setup,
            'before_disable',
            [
                'remove_data' => $this->on_disable_remove_data,
                'remove_config' => $this->on_disable_remove_config
            ]
        );
        if ( $result === false ) {
            \Cli\Util::error('Setup failed for: ' . $pkg);
            return false;
        }

        // If disable successfully terminate further execution!
        if ( ! $this->pkgm->disable($pkg) ) {
            \Cli\Util::error('Failed to disable: ' . $pkg);
            return false;
        }

        // If not successful, print warning, but don't terminate the process
        if ( $this->execute_setup_step($setup, 'after_disable') === false ) {
            \Cli\Util::warn('Problems with: ' . $pkg);
        }

        // Success
        \Cli\Util::success('Disabled: ' . $pkg);
        return true;
    }

    /**
     * Disable particular package, and sub-packages.
     * --
     * @param  string $pkg
     */
    public function action_disable($pkg)
    {
        // Package name is required
        if (!$pkg) {
            return $this->help_disable();
        }

        // Can't disable something that isn't enabled
        if ( ! $this->pkgm->is_enabled($pkg) ) {
            \Cli\Util::warn("Package not enabled: `{$pkg}`.");
            return false;
        }

        // If there's no dependees,
        // just inform the user which package will be disabled.
        \Cli\Util::plain(
            "This will disable the following package: `{$pkg}`."
        );

        // Ask if data should be removed also
        $this->on_disable_remove_data =
            \Cli\Util::confirm('Completely remove all associated data?');
        // Ask if configurations should be removed also
        $this->on_disable_remove_config =
            \Cli\Util::confirm('Completely remove all associated configurations?');

        // Get package dependees!
        $dependees = $this->pkgm->get_dependees($pkg, true);

        // If we have dependees, then disable them all first!
        if (!empty($dependees)) {
            \Cli\Util::plain(
                "The following packages depends on the `{$pkg}` and need to be disabled:\n\n" .
                \Core\Arr::readable($dependees, 2) . "\n"
            );

            if (!\Cli\Util::confirm('Disable listed packages?')) {
                \Cli\Util::plain('Process terminated.');
                return false;
            }

            foreach ($dependees as $dependee) {
                if (!$this->disable_helper($dependee)) {
                    return false;
                }
            }
        }

        // Finally, disable the actual package
        return $this->disable_helper($pkg);
    }

    /**
     * Print help.
     */
    public function help_list()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management :: LIST',
            'packages list <disabled|enabled|obsolete>'
        );
        \Cli\Util::plain('Example, list all disabled packages: packages list disabled');

        return true;
    }

    /**
     * List packages.
     * --
     * @param  string $type enabled|disabled|obsolete
     */
    public function action_list($type = null)
    {
        switch (strtolower($type)) {
            case 'enabled':
                \Cli\Util::nl();
                \Cli\Util::plain(
                    \Core\Arr::readable($this->pkgm->get_enabled(), 2),
                    true
                );
                break;

            case 'disabled':
                \Cli\Util::nl();
                \Cli\Util::plain(
                    \Core\Arr::readable($this->pkgm->get_disabled(), 2),
                    true
                );
                break;

            case 'obsolete':
                \Cli\Util::nl();
                \Cli\Util::plain(
                    \Core\Arr::readable($this->pkgm->get_obsolete(), 2),
                    true
                );
                break;

            default:
                $this->help_list();
        }

        return true;
    }
}
