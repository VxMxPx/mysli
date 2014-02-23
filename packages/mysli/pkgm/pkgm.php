<?php

namespace Mysli;

class Pkgm
{
    // All the enabled packages (loaded from init($filename))
    protected $enabled   = [];
    // All the disabled packages (cached for multiple calls)
    protected $disabled  = [];
    // Package filename (from where list of enabled packages is loaded and
    // where all the modifications are saved)
    protected $filename  = '';
    // Constrcuted packages // kind of a like registry
    protected $cache     = [];
    // Set while creating instance of package (and all its dependencies).
    protected $producing = [];

    /**
     * Init the pkgm class.
     * --
     * @throws DataException If pkgm registry file doesn't contain valid array / json.
     * @throws NotFoundException If pkgm registry file cannot be found.
     * --
     * @return void
     */
    public function __construct()
    {
        $registry = datpath('pkgm/registry.json');

        if (file_exists($registry)) {
            $this->filename = $registry;
            $this->enabled = json_decode(file_get_contents($registry), true);
            if (!is_array($this->enabled)) {
                throw new \Core\DataException(
                    'Invalid packages registry file.'
                );
            }
        } else {
            throw new \Core\NotFoundException(
                "Cannot find the packages registry file: '{$registry}'."
            );
        }

        // Manually load exceptions
        if (!class_exists('Mysli\\Pkgm\\DependencyException', false)) {
            include ds(__DIR__, 'exceptions/dependency.php');
        }
        if (!class_exists('Mysli\\Pkgm\\PackageException', false)) {
            include ds(__DIR__, 'exceptions/package.php');
        }

        // Register itself as an autoloader
        spl_autoload_register([$this, 'autoloader']);

        // Add self to the cache
        $this->cache['mysli/pkgm'] = $this;
    }

    /**
     * Return the list of all enabled packages.
     * --
     * @param  boolean $details Should only packages' names be returned,
     *                          or names and details.
     * --
     * @return array
     */
    public function get_enabled($details = false)
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
     * @param  boolean $details Should only packages' names be returned,
     *                          or names and details.
     * --
     * @return array
     */
    public function get_disabled($details = false)
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
     * @throws NotFoundException If "meta.json" couldn't be found.
     * @throws DataException If "meta.json" is not properly formatted.
     * --
     * @return array
     */
    public function get_details($package)
    {
        if ($this->is_enabled($package)) {
            return $this->enabled[$package];
        }

        // Disabled!
        $meta_file = pkgpath(ds($package, 'meta.json'));
        if (!file_exists($meta_file)) {
            throw new \Core\NotFoundException(
                "Cannot find 'meta.json' file for '{$package}'"
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
     * Accept partial name (e.g. * /pkg_name and return vendor/pkg_name).
     * Accept role name (e.g. ~role and return vendor/pkg_name).
     * You can limit results to only enabled or disabled packages (string).
     * This will stop on first match, if you want to get all packages that
     * match the name, use resolve_all method.
     * --
     * This can also be used to simply check if particular packages exists at all.
     * You can use vendor/pkg for this purpose.
     * --
     * @param  string  $package
     * @param  string  $limit_to      disabled | enabled | null (no limit)
     * @param  boolean $stop_on_match If true, then
     * --
     * @return mixed   string package full name
     *                 false  if not found stop_on_match is true
     *                 empty  array if not found stop_on_match is false
     *                 array  if stop_on_match is false
     */
    public function resolve($package, $limit_to = null, $stop_on_match = true)
    {
        $match = [];
        $regex = false;
        $role = false;

        // Is regex || role?
        if (strpos($package, '*') !== false) {
            $regex =
                '/' .
                str_replace(['*', '/'], ['.*?', '\/'], $package) .
                '/i';
        } elseif (substr($package, 0, 1) === '~') {
            $role = substr($package, 1);
        }

        // If null || disabled
        if ($limit_to !== 'enabled') {
            // If we're looking for role (~), then we need packages' details
            $this->get_disabled(!!$role);
        }

        switch ($limit_to) {
            case 'enabled' :
                $packages = $this->enabled;
                break;

            case 'disabled':
                $packages = $this->disabled;
                break;

            default:
                $packages = array_merge($this->enabled, $this->disabled);
        }

        // No regular expression
        if (!$regex && !$role) {
            if (isset($packages[$package])) return $package;
            else return false;
        } else {
            // Regular expression || role
            foreach ($packages as $package => $data) {
                if ($role) {
                    if (isset($data['role']) && $data['role'] === $role) {
                        if ($stop_on_match) return $package;
                        else $match[] = $package;
                    }
                } elseif (preg_match($regex, $package)) {
                    if ($stop_on_match) return $package;
                    else $match[] = $package;
                }
            }
        }

        return $stop_on_match ? false : $match;
    }

    /**
     * Handle package's main class (construction if possibly).
     * This will auto-manage all dependencies.
     * --
     * This however is not regular factory, it will respect the meta.json's
     * _instantiation_ setting. If the setting is 'once', the class will be
     * constructed only once, if setting is never, this will return null.
     * If setting is 'manual', string will be return (full namespaced class name).
     * This will load class file in all cases.
     * --
     * @param  string $package
     * --
     * @throws ValueException If package is not enabled.
     * @throws DataException If The "instantiation" key is missing in "meta.json"
     * --
     * @return mixed
     */
    public function factory($package)
    {
        // Check if is enabled?
        if (!$this->is_enabled($package)) {
            throw new \Core\ValueException(
                "The package is not enabled: '{$package}'."
            );
        }

        // Check if we have it cached...
        if (isset($this->cache[$package]) && is_object($this->cache[$package])) {
            // $this->log->info(
            //     "The package '{$package}' was found in cache!",
            //     __FILE__, __LINE__
            // );
            return $this->cache[$package];
        }

        // Get package info
        $info = $this->get_details($package);

        // Do we have the index?
        if (!isset($info['instantiation'])) {
            throw new \Core\DataException(
                "The 'instantiation' key is missing in meta for: '{$package}'."
            );
        }

        // Check the instantiation instructions
        if ($info['instantiation'] === 'never') {
            // $this->log->info(
            //     "The package '{$package}' will not be auto instantiated. " .
            //     "The 'instantiation' is set to 'never'.",
            //     __FILE__, __LINE__
            // );
            return false;
        }

        // Check if we have class and if not, fetch it ...
        $class = $this->pkg_to_ns($package);

        if (!class_exists($class, false)) {
            if (!$this->load($package)) {
                return false;
            }
        }

        // Check if we have manual instruction for instantiation
        if ($info['instantiation'] === 'manual') {
            // $this->log->info(
            //     "The package '{$package}' should be manually instantiated. ".
            //     "The 'instantiation' is set to 'manual'. " .
            //     "Will return a class name.",
            //     __FILE__, __LINE__
            // );
            return $class;
        }

        // Instantiate class now...
        // Push current package name to production line...
        $this->producing[] = $package;

        $object = new \ReflectionClass($class);
        if ($object->hasMethod('__construct')) {
            try {
                $dependencies = $this->dependencies_factory($info['inject']['main']);
            } catch (\Exception $e) {
                $this->producing = [];
                throw $e;
            }
            $object = $object->newInstanceArgs($dependencies);
        } else {
            $object = $object->newInstanceWithoutConstructor();
        }

        // Do we have instruction to be instantiated once?
        if ($info['instantiation'] === 'once') {
            $this->cache[$package] = $object;
        }

        array_pop($this->producing);

        return $object;
    }

    /**
     * Construct dependencies from the list. Will return an array of objects.
     * --
     * @param  array $dependencies
     * --
     * @throws DependencyException If can't get details for dependency. (10)
     * @throws DependencyException If dependency is missing. (20)
     * @throws DependencyException If dependency is not enabled. (30)
     * @throws DependencyException If dependency version is not sufficient. (40)
     * --
     * @return array
     */
    public function dependencies_factory(array $dependencies)
    {
        $result = [];

        foreach ($dependencies as $dependency => $version) {

            // Check if we have #<speacial instruction>
            if (substr($dependency, 0 ,1) === '#') {
                if ($dependency === '#pkgm_trace') {
                    $result[$dependency] = $this->producing;
                } else {
                    $result[$dependency] = null;
                }
                continue;
            }

            // Resolve the dependency
            $dependency_resovled = $this->resolve($dependency, 'enabled');

            // Check if we have match
            if (!$dependency_resovled) {
                throw new \Mysli\Pkgm\DependencyException(
                    "Cannot get dependency: '{$dependency}' version: '{$version}'.",
                    20
                );
            }
            // Check if is enabled
            if (!$this->is_enabled($dependency_resovled)) {
                throw new \Mysli\Pkgm\DependencyException(
                    "Dependency is not enabled: '{$dependency}' cannot proceed.",
                    30
                );
            }
            // Check if version is OK
            $resolved_version = $this->get_details($dependency_resovled)['version'];
            if (!\Core\Int::compare_versions($resolved_version, $version)) {
                throw new \Mysli\Pkgm\DependencyException(
                    "Dependency version is not correct. ".
                    "Version: '{$resolved_version}' required: '{$version}'.",
                    40
                );
            }
            $object = $this->factory($dependency_resovled);
            if ($object !== false) {
                $result[explode('/', $dependency_resovled)[1]] = $object;
            }
        }
        return $result;
    }

    /**
     * Get and construct the setup file for particular package.
     * --
     * @param  string $package
     * --
     * @throws DataException If setup class is not found.
     * --
     * @return mixed  Object (setup) or false
     */
    public function construct_setup($package)
    {
        $package_vendor = explode('/', $package)[0];
        $package_name   = explode('/', $package)[1];

        // Resolve class name with namespace!
        $setup_class_name = \Core\Str::to_camelcase($package_vendor) .
                            '\\' .
                            \Core\Str::to_camelcase($package_name) .
                            '\\' .
                            'Setup';

        // Does setup file exists
        if (!file_exists(pkgpath(ds($package, 'setup.php')))) {
            return false;
        }

        // $this->log->info("Setup was found for '{$package}'.", __FILE__, __LINE__);

        // Can we find setup class?
        if (!class_exists($setup_class_name, false)) {
            include(pkgpath(ds($package, 'setup.php')));
            if (!class_exists($setup_class_name, false)) {
                throw new \Core\DataException(
                    "Class not found '{$setup_class_name}'."
                );
            }
        }

        // Get info!
        $info = $this->get_details($package);

        // If this is disabled package,
        // then we need to resolve dependencies => inject.
        if (!$this->is_enabled($package)) {
            $info = $this->process_injections($info);
        }

        // Construct and return
        $this->producing[] = $package;
        $object = new \ReflectionClass($setup_class_name);
        if ($object->hasMethod('__construct')) {
            try {
                $dependencies = $this->dependencies_factory($info['inject']['setup']);
            } catch (\Exception $e) {
                $this->producing = [];
                throw $e;
            }
            $instance = $object->newInstanceArgs($dependencies);
        } else {
            $instance = $object->newInstanceWithoutConstructor();
        }

        array_pop($this->producing);
        return $instance;
    }

    /**
     * This is (in core registered) autoloader.
     * --
     * @param  string $class
     * --
     * @throws PackageException If trying to load sub class of package which is not enabled.
     * @throws NotFoundException If required file for class is not found.
     * --
     * @return void
     */
    public function autoloader($class)
    {
        // Cannot handle non-namespaced packages
        if (strpos($class, '\\') === false && strpos($class, '/') === false) {
            return;
        }

        // Perhaps it's a sub-class of a package? (\Mysli\Package\SubClass)
        if (substr_count($class, '\\') > 1) {

            // Create path e.g.: mysli/package/sub_class from class name
            $path = $this->ns_to_pkg($class);
            // Create an array of path segments
            $segments = explode('/', $path);
            // The actual package name consist of first two segments
            $package = $segments[0] . '/' . $segments[1];

            // The package must be enabled...
            if (!$this->is_enabled($package)) {
                return;
                // throw new \Mysli\Pkgm\PackageException(
                //     "Cannot load class: `{$class}`, ".
                //     "package is not enabled: `{$package}`.",
                //     1
                // );
            }

            // Are we dealing with exception?
            if (substr($class, -9, 9) === 'Exception') {
                $file = array_pop($segments);
                $file = substr($file, 0, -10);
                $path = pkgpath(implode('/', $segments) . '/exceptions/' . $file . '.php');
            } else {
                $path = pkgpath($path . '.php');
            }

            // We don't have file?
            if (!file_exists($path)) {
                throw new \Core\NotFoundException(
                    "Cannot find file for class: `{$class}` in: `{$path}`.", 1
                );
            }

            // All is ok, include the file.
            include $path;
        } else {
            try {
                $package = $this->ns_to_pkg($class);
                $this->load($package);
            } catch (\Exception $e) {
                return;
            }
        }
    }

    /**
     * Load package's main class.
     * --
     * @param  string  $package
     * @param  boolean $force   Will load package even if it's disabled!
     * --
     * @throws \Mysli\Pkgm\PackageException If package name is invalid / cannot
     *                                      be resolved.
     * --
     * @return boolean
     */
    public function load($package, $force = false)
    {
        $class = $this->pkg_to_ns($package);
        $package_segments = explode('/', $package);
        if (!isset($package_segments[1])) {
            throw new \Mysli\Pkgm\PackageException(
                "Invalid package: `{$package}`.", 1
            );
        }
        $package_base = $package_segments[0] . '/' . $package_segments[1];

        if (class_exists($class, false)) { return true; }

        if (!$this->is_enabled($package_base) && !$force) {
            // $this->log->info(
            //     "Cannot load the class: '{$class}', " .
            //     "because the package is not enabled.",
            //     __FILE__, __LINE__
            // );
            return false;
        }

        if (count($package_segments) === 2) {
            $filename = pkgpath(ds($package, explode('/', $package)[1] . '.php'));
        } else {
            $filename = pkgpath(ds($package . '.php'));
        }

        if (!file_exists($filename)) {
            // $this->log->info(
            //     "File not found: '{$filename}'.",
            //     __FILE__, __LINE__
            // );
            return false;
        }

        include $filename;
        return class_exists($class, false);
    }

    /**
     * Call package's method. Will automaticall load and construct it if needed,
     * --
     * @param  string $package
     * @param  string $method
     * @param  array  $params
     * --
     * @throws ValueException If package couldn't be constructed. (10)
     * @throws ValueException If required method doesn't exists. (20)
     * --
     * @return mixed  Result of the execution.
     */
    public function call($package, $method, array $params = [])
    {
        $object = $this->factory($package);

        if (!is_object($object)) {
            throw new \Core\ValueException(
                "Could not construct the package: '{$package}'.",
                10
            );
        }

        if (!method_exists($object, $method)) {
            throw new \Core\ValueException(
                "Required method doesn't exists: '{$method}' for '{$package}'.",
                20
            );
        }

        return call_user_func_array([$object, $method], $params);
    }

    /**
     * Take package name (vendor/pkg) and return namespace + class (Vendor/Class).
     * --
     * @param  string $package
     * --
     * @return string
     */
    public function pkg_to_ns($package)
    {
        if (strpos($package, '/') === false) {
            return $package;
        }

        $class = \Core\Str::to_camelcase($package);
        $class = str_replace('/', '\\', $class);

        return $class;
    }

    /**
     * Take the namespaced class name (Vendor/Class)
     * and return package name (vendor/pkg).
     * --
     * @param  string $class
     * --
     * @return string
     */
    public function ns_to_pkg($class)
    {
        if (strpos($class, '\\') === false) {
            return $class;
        }

        $package = \Core\Str::to_underscore($class);
        $package = strtolower($package);
        $package = str_replace('\\', '/', $package);

        return $package;
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
        return isset($this->enabled[$package]);
    }

    /**
     * This will get all the dependencies of provided package.
     * If you set $deep to true, it will resolve deeper relationships,
     * e.g. dependencies of dependencies.
     * --
     * @param  string  $package
     * @param  boolean $deep
     * --
     * @throws DataException If details for package couldn't be fetched.
     * --
     * @return array
     */
    public function get_dependencies($package, $deep = false)
    {
        $details = $this->get_details($package);
        if (!is_array($details) || empty($details)) {
            throw new \Core\DataException(
                "Could not get details for '{$package}'."
            );
        }

        $dependencies = [
            'enabled'  => [],
            'disabled' => [],
            'missing'  => []
        ];

        foreach ($details['depends_on'] as $dependency => $version) {
            $dependency_resovled = $this->resolve($dependency);
            if (!$dependency_resovled) {
                $dependencies['missing'][$dependency] = $version;
            }
            else {
                if ($this->is_enabled($dependency_resovled)) {
                    $dependencies['enabled'][$dependency_resovled] = $version;
                } else {
                    $dependencies['disabled'][$dependency_resovled] = $version;
                }
            }
        }

        if (!$deep) return $dependencies;

        foreach ($dependencies['disabled'] as $dependency => $version) {
            $dependency_resovled = $this->resolve($dependency);
            if (!$dependency_resovled) {
                $dependencies['missing'] = array_merge(
                    [$dependency => $version],
                    $dependencies['missing']
                );
            } else {
                if ($this->is_enabled($dependency_resovled)) {
                    $dependencies['enabled'][$dependency_resovled] = $version;
                } else {
                    $dependencies = \Core\Arr::merge(
                        $this->get_dependencies($dependency_resovled, true),
                        $dependencies
                    );
                }
            }
        }

        return $dependencies;
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
    public function get_dependees($package, $deep = false)
    {
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
                $dependees,
                $this->get_dependees($dependee, true)
            );
        }

        return array_unique($dependees);
    }

    /**
     * Will disable particular package. Please note that this won't resolve
     * dependencies, you must do that manually.
     * This also won't call the setup automatically!
     * --
     * @param  string  $package
     * --
     * @throws ValueException If package is already disabled.
     * --
     * @return boolean
     */
    public function disable($package)
    {
        // Is enabled at all?
        if (!$this->is_enabled($package)) {
            throw new \Core\ValueException(
                "Cannot disable package: '{$package}' it's not enabled."
            );
        }

        $info = $this->get_details($package);

        // Is required by other package?
        // if (!empty($info['required_by'])) {
        //     $this->log->info(
        //         "Will disable package: '{$package}' ".
        //         "despite the fact that it's required by: " .
        //         dump_r($info['required_by']),
        //         __FILE__, __LINE__
        //     );
        // }

        // Remove itself from required_by
        foreach ($info['depends_on'] as $dependency => $version) {
            $dependency = $this->resolve($dependency, 'enabled');
            if ($dependency) {
                if (in_array($package, $this->enabled[$dependency]['required_by'])) {
                    unset(
                        $this->enabled[$dependency]['required_by'][
                            array_search(
                                $package,
                                $this->enabled[$dependency]['required_by'])
                        ]
                    );
                }
            }
        }

        // Remove the main key
        if (isset($this->enabled[$package])) {
            unset($this->enabled[$package]);
        }

        // Save changes
        return $this->registry_save();
    }

    /**
     * Will properly process inject section of info file.
     * --
     * @param  array  $info
     * --
     * @return array
     */
    protected function process_injections(array $info)
    {
        if (!isset($info['inject']) || !is_array($info['inject'])) {
            $info['inject'] = [];
        }

        // Get exclude if exists.
        $exclude = \Core\Arr::element('exclude', $info['inject'], []);
        if (!is_array($exclude)) {
            trigger_error(
                'Badly formatted `meta.json`, for: `' . $info['package'] .
                '`. Make sure that [inject][exclude] is valid array.',
                E_USER_WARNING
            );
            $exclude = [$exclude];
        }

        // Dependencies minus exclusion
        $depends_on = $info['depends_on'];
        if (!isset($info['inject']['main'])) $info['inject']['main']   = array_keys($depends_on);
        if (!isset($info['inject']['setup'])) $info['inject']['setup'] = array_keys($depends_on);

        foreach ($info['inject'] as $section => $packages) {
            if ($section === 'exclude') continue;
            if (!empty($exclude)) {
                $info['inject'][$section] = array_diff(
                    $info['inject'][$section],
                    $exclude
                );
                $packages = $info['inject'][$section];
            }
            if (empty($packages)) { continue; }
            $info['inject'][$section] = [];
            foreach ($packages as $package) {
                if (isset($depends_on[$package])) {
                    // Set version!
                    $info['inject'][$section][$package] = $depends_on[$package];
                } elseif (substr($package, 0, 1) === '#') {
                    // Special directive.
                    // No version needed in this case.
                    $info['inject'][$section][$package] = 0;
                } else {
                    trigger_error(
                        'Badly formatted `meta.json`. Package in inject `'.
                        $package . '` is not included in `depends_on` array.',
                        E_USER_WARNING
                    );
                }
            }
        }

        return $info;
    }

    /**
     * Will enable particular package. Please note that this won't resolve
     * dependencies, you must do that manually.
     * This also won't call the setup automatically!
     * --
     * @param  string $package
     * --
     * @throws NotFoundException If can't find main package class.
     * --
     * @return boolean
     */
    public function enable($package)
    {
        // Resolve the path
        $package_path   = pkgpath($package);
        $package_vendor = explode('/', $package)[0];
        $package_name   = explode('/', $package)[1];

        // Check if main class file exists
        if (!file_exists(ds($package_path, $package_name.'.php'))) {
            throw new \Core\NotFoundException(
                "Cannot find main package's class '{$package_name}.php' ".
                "in '{$package_path}'."
            );
        }

        // Get info!
        $info = $this->get_details($package);

        // Add new required_by key
        foreach ($info['depends_on'] as $dependency => $version) {
            $dependency = $this->resolve($dependency, 'enabled');
            if ($dependency) {
                $this->enabled[$dependency]['required_by'][] = $package;
            }
        }

        // Process injections
        $info = $this->process_injections($info);

        // Add package's details to the register
        $info['required_by'] = [];
        $this->enabled[$package] = $info;

        return $this->registry_save();
    }

    /**
     * Save changes (when enabling / disabling the package) to the registry file.
     * --
     * @return boolean
     */
    protected function registry_save()
    {
        return file_put_contents($this->filename, json_encode($this->enabled));
    }
}
