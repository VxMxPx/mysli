<?php

namespace Mysli\Pkgm;

class Registry
{
    /**
     * Meta filename (full absolute path).
     * --
     * @var string
     */
    private $filename;

    /**
     * Whole registery file.
     * --
     * @var array
     */
    private $registry;

    /**
     * Registry file contents.
     * --
     * @var array
     */
    private $enabled;

    /**
     * List of roles.
     * --
     * @var array
     */
    private $roles;

    /**
     * List of disabled packages.
     * --
     * @var array
     */
    private $disabled;

    /**
     * Instance of Registry.
     * --
     * @param string $filename
     */
    public function __construct($filename)
    {
        if (file_exists($filename)) {
            $this->filename = $filename;
            $this->registry = json_decode(file_get_contents($filename), true);
            if (!is_array($this->registry)) {
                throw new \Core\DataException(
                    'Invalid packages registry file.'
                );
            }

            $this->enabled = &$this->registry['enabled'];
            $this->roles   = &$this->registry['roles'];

        } else {
            throw new \Core\NotFoundException(
                "Cannot find the packages registry file: '{$filename}'."
            );
        }
    }

    /**
     * Check if particular package is enabled.
     * --
     * @param  string  $package
     * --
     * @return boolean
     */
    public function is_enabled($package)
    {
        $package = $this->get_role($package);
        return isset($this->enabled[$package]);
    }

    /**
     * Try to find package in file system.
     * --
     * @param  string $package
     * --
     * @return boolean
     */
    public function exists($package)
    {
        $package = $this->get_role($package);
        return file_exists(pkgpath($package, 'mysli.pkg.json'));
    }

    /**
     * Return the list of all enabled packages.
     * --
     * @param  boolean $details
     * - true: return packages' names and details
     * - false: return only packages' names
     * --
     * @return array
     */
    public function list_enabled($details = false)
    {
        if (!$details) {
            return array_keys($this->enabled);
        } else {
            return $this->enabled;
        }
    }


    /**
     * Return the list of all disabled packages.
     * --
     * @param  boolean $details
     * - true: return packages' names and details
     * - false: return only packages' names
     * --
     * @return array
     */
    public function list_disabled($details = false)
    {
        if (!empty($this->disabled)) {
            if ($details) {
                if (is_array(\Core\Arr::first($this->disabled))) {
                    return $this->disabled;
                }
            } else {
                return array_keys($this->disabled);
            }
        }

        $disabled = [];
        $vendors  = scandir(pkgpath());

        foreach ($vendors as $vendor) {
            if (substr($vendor, 0, 1) === '.') continue;

            $vendor_packages = scandir(pkgpath($vendor));

            foreach ($vendor_packages as $package_name) {
                if (substr($package_name, 0, 1) === '.') continue;

                $package = $vendor . '/' . $package_name;

                if ($this->is_enabled($package)) continue;

                // Will use array_keys to get proper array if details not required...
                $disabled[$package] = true;

                if (!$details) continue;

                $disabled[$package] = $this->get_details($package);
            }
        }

        $this->disabled = $disabled;
        return !$details ? array_keys($this->disabled) : $this->disabled;
    }


    /**
     * Get details for particular (either enabled or disabled) package.
     * --
     * @param  string $package
     * --
     * @throws NotFoundException If "mysli.pkg.json" couldn't be found.
     * @throws DataException If "mysli.pkg.json" is not properly formatted.
     * --
     * @return array
     */
    public function get_details($package)
    {
        $package = $this->get_role($package);

        if ($this->is_enabled($package)) {
            return $this->enabled[$package];
        }

        // Disabled!

        $meta_file = pkgpath(ds($package, 'mysli.pkg.json'));
        if (!file_exists($meta_file)) {
            throw new \Core\NotFoundException(
                "Cannot find 'mysli.pkg.json' file for '{$package}'"
            );
        }

        $meta = json_decode(file_get_contents($meta_file), true);
        if (!is_array($meta)
            || !isset($meta['package'])
            || $meta['package'] !== $package
        ) {
            throw new \Core\DataException(
                "Meta file for '{$package}' seems to be invalid: " .
                dump_r($meta)
            );
        }

        return $meta;
    }


    /**
     * This will get all the dependencies of provided package.
     * If you set $deep to true, it will resolve deeper relationships,
     * e.g. dependencies of dependencies.
     * --
     * @param  string  $package
     * @param  boolean $deep
     * @param  array   $process Use internally. Prevent infinite loop,
     * if cross dependency situation occurs (a require b and b require a).
     * --
     * @throws DependencyException If there's cross reference - which could cause infinite loop.
     * @throws DataException If details for package couldn't be fetched.
     * --
     * @return array
     */
    public function list_dependencies($package, $deep = false, array $process = [])
    {
        $details = $this->get_details($package);

        if (!is_array($details) || empty($details)) {
            throw new \Core\DataException(
                "Could not get details for '{$package}'."
            );
        }

        $list = [
            'enabled'  => [],
            'disabled' => [],
            'missing'  => []
        ];

        foreach ($details['depends_on'] as $dependency => $version) {
            if (!$this->exists($dependency)) {
                $list['missing'][$dependency] = $version;
            } else {
                if ($this->is_enabled($dependency)) {
                    $list['enabled'][$dependency] = $version;
                } else {
                    $list['disabled'][$dependency] = $version;
                }
            }
        }

        if (!$deep) return $list;

        // Check if we don't have infinite loop!
        $hash = $package . ': ' . implode(', ', array_keys($details['depends_on']));
        if (in_array($hash, $process)) {
            $process[count($process) - 1] = ' >> ' . $process[count($process) - 1];
            $process[] = ' >> ' . $hash;
            array_unshift($process, '----------');
            $process[] = '----------';
            throw new DependencyException(
                "Infinite loop, cross dependencies...\n" . implode("\n", $process)
            );
        }
        $process[] = $hash;

        foreach ($list['disabled'] as $dependency => $version) {
            $nlist = $this->list_dependencies($dependency, true, $process);

            $list['enabled']  = ( array_merge($nlist['enabled'],  $list['enabled']) );
            $list['disabled'] = ( array_merge($nlist['disabled'], $list['disabled']) );
            $list['missing']  = ( array_merge($nlist['missing'],  $list['missing']) );
        }

        return $list;
    }

    /**
     * This will get all the dependees (the packages which requires provided
     * package, e.g. are dependent on it!)
     * If you set $deep to true, it will resolve deeper relationships,
     * e.g. dependees of dependees.
     * --
     * @param  string  $package
     * @param  boolean $deep
     * --
     * @throws DataException If details for package couldn't be fetched.
     * --
     * @return array
     */
    public function list_dependees($package, $deep = false, array &$listed = [])
    {
        if (in_array($package, $listed)) { return []; }
        $listed[] = $package;

        $details = $this->get_details($package);

        if (!is_array($details) || empty($details)) {
            throw new \Core\DataException(
                "Could not get details for '{$package}'."
            );
        }

        if (!$deep) return $details['required_by'];

        $dependees = [];

        foreach ($details['required_by'] as $dependee) {
            $dependees[] = $dependee;
            array_merge(
                $this->list_dependees($dependee, true, $listed),
                $dependees
            );
        }

        return array_unique( array_reverse($dependees) );
    }

    /**
     * Get list of packages which were automatically enabled
     * and are not required anymore.
     * --
     * @return array
     */
    public function list_obsolete()
    {
        $enabled = $this->enabled;
        $obsolete = [];

        do {
            $chg = false;

            foreach ($enabled as $pkg_id => &$pkg_data) {
                if (!empty($pkg_data['required_by'])) {
                    foreach ($pkg_data['required_by'] as $id => $req_pkg) {
                        if (in_array($req_pkg, $obsolete)) {
                            unset($pkg_data['required_by'][$id]);
                            $chg = true;
                        }
                    }
                }
                if (empty($pkg_data['required_by']) && $pkg_data['enabled_by']) {
                    unset($enabled[$pkg_id]);
                    $obsolete[] = $pkg_id;
                    $chg = true;
                }
            }

        } while ($chg);

        return $obsolete;
    }

    /**
     * Remove package (entry) from list of enabled packages.
     * This will NOT save changes. This will NOT take care of dependee.
     * Use instead: $pkgm->control($pkg)->disable()
     * --
     * @param  string $package
     * --
     * @return null
     */
    public function remove_package($package)
    {
        if (isset($this->enabled[$package])) {
            unset($this->enabled[$package]);
        }
    }

    /**
     * Add package (entry) to the list of enabled packages.
     * This will NOT save changes. This will NOT take care of dependencies.
     * If package is on the list already, this will replace it!
     * --
     * @param string $package
     * @param array  $meta
     * --
     * @return null
     */
    public function add_package($package, $meta)
    {
        $this->enabled[$package] = $meta;
    }

    /**
     * Add package's dependee.
     * --
     * @param string $package
     * @param string $dependee
     * --
     * @return null
     */
    public function add_dependee($package, $dependee)
    {
        // Resolve dependee before adding it!
        $package = $this->get_role($package);

        if (!$this->is_enabled($package)) { return; }
        $this->enabled[$package]['required_by'][] = $dependee;
    }

    /**
     * Will remove particular dependee from package.
     * --
     * @param  string $package
     * @param  string $dependee
     * --
     * @return null
     */
    public function remove_dependee($package, $dependee)
    {
        if (!$this->is_enabled($package)) { return; }

        if (in_array($dependee, $this->enabled[$package]['required_by'])) {
            unset(
                $this->enabled[$package]['required_by'][
                    array_search(
                        $dependee,
                        $this->enabled[$package]['required_by']
                    )
                ]
            );
        }
    }

    /**
     * Set core role.
     * --
     * @param string $role
     * @param string $package
     * --
     * @return null
     */
    public function set_role($role, $package)
    {
        if (substr($role, 0, 1) !== '@') { return; }
        $this->roles[$role] = $package;
    }

    /**
     * Unset core role.
     * --
     * @param string $role
     * --
     * @return null
     */
    public function unset_role($role)
    {
        if (substr($role, 0, 1) !== '@') { return; }
        if (isset($this->roles[$role])) {
            unset($this->roles[$role]);
        }
    }

    /**
     * Get package's role.
     * --
     * @param string $role
     * --
     * @return null
     */
    public function get_role($role)
    {
        if (substr($role, 0, 1) !== '@') { return $role; }

        if (isset($this->roles[$role])) {
            return $this->roles[$role];
        }

        // Try to find it in disabled stack...
        foreach ($this->list_disabled(true) as $package => $meta) {
            if (isset($meta['role']) && $meta['role'] === $role) {
                return $package;
            }
        }
    }

    /**
     * Save registry changes to file!
     * --
     * @return boolean
     */
    public function save()
    {
        return file_put_contents($this->filename, json_encode($this->registry));
    }
}
