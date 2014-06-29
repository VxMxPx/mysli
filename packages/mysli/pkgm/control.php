<?php

namespace Mysli\Pkgm;

class Control
{
    /**
     * Package name.
     * --
     * @var string
     */
    private $package;

    /**
     * Registry object.
     * --
     * @var \Mysli\Pkgm\Registry
     */
    private $registry;

    /**
     * New instace of control.
     * --
     * @param string               $package
     * @param \Mysli\Pkgm\Registry $registry
     */
    public function __construct($package, $registry)
    {
        // Resolve role
        // if (substr($package, 0, 1) === '@') {
        //     $package = $registry->get_role($package);
        // }
        $this->package  = $package;
        $this->registry = $registry;
    }

    /**
     * Enable particular package. Please note that this won't resolve
     * dependencies, you must do that manually.
     * This also won't call the setup automatically!
     * --
     * @param  string $enabled_by Which package enabled this one.
     *                            Can be null if this package was enabled
     *                            directly by user.
     *                            This will be used for cleanup method.
     * --
     * @throws \Core\ValueException If package already enabled.
     * --
     * @return boolean
     */
    public function enable($enabled_by = null)
    {
        if ($this->registry->is_enabled($this->package)) {
            throw new \Core\ValueException("Package (`{$this->package}`) is already enabled!", 1);
        }

        // Get info!
        $info = $this->registry->get_details($this->package);

        // Add new required_by key
        foreach ($info['require'] as $dependency => $version) {
            $this->registry->add_dependee($dependency, $this->package);
        }

        // Process injections
        // if (isset($info['factory']) && is_array($info['factory'])) {
        //     foreach ($info['factory'] as $file => $factory_info) {
        //         $info['factory'][$file] = self::process_factory_entry($factory_info);
        //     }
        // }

        // Set role if there
        // if (isset($info['role'])) {
        //     $this->registry->set_role($info['role'], $this->package);
        // }

        // Add enabled by info
        $info['enabled_by']  = $enabled_by;
        $info['enabled_on']  = (int) gmdate('Ymd');
        $info['required_by'] = [];

        // Does any of the enabled packages require this package?
        // As strange as this might seems this will happened sometimes -
        // especially when replacing packages.
        foreach ($this->registry->list_enabled(true) as $lpkg => $lmeta)
            foreach ($lmeta['require'] as $depends_on => $version)
                if ($depends_on === $this->package)
                    $info['required_by'][] = $lpkg;

        $this->registry->add_package($this->package, $info);
        return $this->registry->save();
    }


    /**
     * Will disable particular package. Please note that this won't resolve
     * dependencies, you must do that manually.
     * This also won't call the setup automatically!
     * --
     * @throws ValueException If package is already disabled.
     * --
     * @return boolean
     */
    public function disable()
    {
        // Is enabled at all?
        if (!$this->registry->is_enabled($this->package)) {
            throw new \Core\ValueException(
                "Cannot disable package: '{$this->package}' it's not enabled."
            );
        }

        $info = $this->registry->get_details($this->package);

        // Remove role if there
        // if (isset($info['role'])) {
        //     $this->registry->unset_role($info['role']);
        // }

        // Remove itself from required_by
        foreach ($info['require'] as $dependency => $version) {
            $this->registry->remove_dependee($dependency, $this->package);
        }

        // Remove itself
        $this->registry->remove_package($this->package);

        // Save changes
        return $this->registry->save();
    }

    /**
     * Process actory entry and return array.
     * e.g.: singleton(one, two) => [
     *     'instantiation' => 'singletone',
     *     'inject'        => ['one', 'two']
     * ]
     * --
     * @param  mixed $entry String or array.
     * --
     * @return array
     */
    // public static function process_factory_entry($entry)
    // {
    //     if (is_string($entry)) {
    //         $entry_r = explode('(', $entry, 2);
    //         $entry   = [
    //             'instantiation' => trim($entry_r[0]),
    //             'inject'        => trim($entry_r[1], ')')
    //         ];
    //     }

    //     if (is_string($entry['inject'])) {
    //         $entry['inject'] = \Core\Str::explode_trim(',', $entry['inject']);
    //     }

    //     if (count($entry['inject']) === 1 && empty($entry['inject'][0])) {
    //         $entry['inject'] = [];
    //     }

    //     return $entry;
    // }
}
