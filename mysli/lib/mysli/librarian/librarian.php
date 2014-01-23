<?php

namespace Mysli;

class Librarian
{
    // All the enabled libraries (loaded from init($filename))
    protected $enabled   = [];
    // All the disabled libraries (cached for multiple calls)
    protected $disabled  = [];
    // Libraries filename (from where list of enabled libraries is loaded and
    // where all the modifications are saved)
    protected $filename  = '';
    // Constrcuted libraries // kind of a like registry
    protected $cache     = [];

    /**
     * Init the librarian class. Will accept filename of
     * of registry file, containing currently enabled libraries.
     * --
     * @throws \Core\DataException If libraries registry file doesn't contain valid array / json.
     * @throws \Core\FileSystemException If libraries registry file cannot be found.
     * --
     * @return void
     */
    public function __construct()
    {
        $filename = datpath('librarian/registry.json');

        if (file_exists($filename)) {
            $this->filename = $filename;
            $this->enabled = json_decode(file_get_contents($filename), true);
            if (!is_array($this->enabled)) {
                throw new \Core\DataException(
                    'Invalid libraries registry file.'
                );
            }
        } else {
            throw new \Core\FileNotFoundException(
                "Cannot find the libraries registry file: '{$filename}'."
            );
        }
    }

    /**
     * Return the list of all enabled libraries.
     * --
     * @param  boolean $details Should only libraries' names be returned,
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
     * Return the list of all disabled libraries.
     * --
     * @param  boolean $details Should only libraries' names be returned,
     *                          or names and details.
     * --
     * @return array
     */
    public function get_disabled($details = false)
    {
        if (!empty($this->disabled)) {
            if ($details) {
                if (is_array(\Arr::first($this->disabled))) {
                    return $this->disabled;
                }
            } else {
                return array_keys($this->disabled);
            }
        }

        $disabled = [];
        $vendors  = scandir(libpath());

        foreach ($vendors as $vendor) {
            if (substr($vendor, 0, 1) === '.') continue;
            $vendor_libraries = scandir(libpath($vendor));

            foreach ($vendor_libraries as $library_name) {
                if (substr($library_name, 0, 1) === '.') continue;

                $library = $vendor . '/' . $library_name;

                if ($this->is_enabled($library)) continue;

                $disabled[$library] = true;

                if (!$details) continue;

                $disabled[$library] = $this->get_details($library);
            }
        }

        $this->disabled = $disabled;
        return !$details ? array_keys($this->disabled) : $this->disabled;
    }

    /**
     * Get details for particular (either enabled or disabled) library.
     * --
     * @param  string $library
     * --
     * @return array
     */
    public function get_details($library)
    {
        if ($this->is_enabled($library)) {
            return $this->enabled[$library];
        }

        // Disabled!
        $meta_file = libpath(ds($library, 'meta.json'));
        if (!file_exists($meta_file)) {
            throw new \Core\FileNotFoundException(
                "Cannot find 'meta.json' file for '{$library}'"
            );
        }

        $meta = json_decode(file_get_contents($meta_file), true);
        if (!is_array($meta)
            || !isset($meta['library'])
            || $meta['library'] !== $library
        ) {
            throw new \Core\DataException(
                "Meta file for '{$library}' seems to be invalid: " .
                dump_r($meta)
            );
        }

        return $meta;
    }

    /**
     * Accept partial name (e.g. * /lib_name and return vendor/lib_name).
     * Accept role name (e.g. ~role and return vendor/lib_name).
     * You can limit results to only enabled or disabled libraries (string).
     * This will stop on first match, if you want to get all libraries that
     * match the name, use resolve_all method.
     * --
     * This can also be used to simply check if particular library exists at all.
     * You can use vendor/lib for this purpose.
     * --
     * @param  string  $library
     * @param  string  $limit_to      disabled | enabled | null (no limit)
     * @param  boolean $stop_on_match If true, then
     * --
     * @return mixed   string lib full name
     *                 false if not found stop_on_match is true
     *                 empty array if not found stop_on_match is false
     *                 array if stop_on_match is false
     */
    public function resolve($library, $limit_to = null, $stop_on_match = true)
    {
        $match = [];
        $regex = false;
        $role = false;

        // Is regex || role?
        if (strpos($library, '*') !== false) {
            $regex =
                '/' .
                str_replace(['*', '/'], ['.*?', '\/'], $library) .
                '/i';
        } elseif (substr($library, 0, 1) === '~') {
            $role = substr($library, 1);
        }

        // If null || disabled
        if ($limit_to !== 'enabled') {
            // If we're looking for role (~), then we need libraries' details
            $this->get_disabled(!!$role);
        }

        switch ($limit_to) {
            case 'enabled' :
                $libraries = $this->enabled;
                break;

            case 'disabled':
                $libraries = $this->disabled;
                break;

            default:
                $libraries = array_merge($this->enabled, $this->disabled);
        }

        // No regular expression
        if (!$regex && !$role) {
            if (isset($libraries[$library])) return $library;
            else return false;
        } else {
            // Regular expression || role
            foreach ($libraries as $library => $data) {
                if ($role) {
                    if (isset($data['role']) && $data['role'] === $role) {
                        if ($stop_on_match) return $library;
                        else $match[] = $library;
                    }
                } elseif (preg_match($regex, $library)) {
                    if ($stop_on_match) return $library;
                    else $match[] = $library;
                }
            }
        }

        return $stop_on_match ? false : $match;
    }

    /**
     * Handle library's main class (construction if possibly).
     * This will auto-manage all dependencies.
     * --
     * This however is not regular factory, it will respect the meta.json's
     * _instantiation_ setting. If the setting is 'once', the class will be
     * constructed only once, if setting is never, this will return null.
     * If setting is 'manual', string will be return (full namespaced class name).
     * This will load class file in all cases.
     * --
     * @param  string $library
     * --
     * @return mixed
     */
    public function factory($library)
    {
        // Check if is enabled?
        if (!$this->is_enabled($library)) {
            throw new \Core\ValueException(
                "The library is not enabled: '{$library}'."
            );
        }

        // Check if we have it cached...
        if (isset($this->cache[$library]) && is_object($this->cache[$library])) {
            // $this->log->info(
            //     "The library '{$library}' was found in cache!",
            //     __FILE__, __LINE__
            // );
            return $this->cache[$library];
        }

        // Get library info
        $info = $this->get_details($library);

        // Do we have the index?
        if (!isset($info['instantiation'])) {
            throw new \Core\DataException(
                "The 'instantiation' key is missing in meta for: '{$library}'."
            );
        }

        // Check the instantiation instructions
        if ($info['instantiation'] === 'never') {
            // $this->log->info(
            //     "The library '{$library}' will not be auto instantiated. " .
            //     "The 'instantiation' is set to 'never'.",
            //     __FILE__, __LINE__
            // );
            return false;
        }

        // Check if we have class and if not, fetch it ...
        $class = $this->lib_to_ns($library);

        if (!class_exists($class, false)) {
            if (!$this->load($library)) {
                return false;
            }
        }

        // Check if we have manual instruction for instantiation
        if ($info['instantiation'] === 'manual') {
            // $this->log->info(
            //     "The library '{$library}' should be manually instantiated. ".
            //     "The 'instantiation' is set to 'manual'. " .
            //     "Will return a class name.",
            //     __FILE__, __LINE__
            // );
            return $class;
        }

        // Instantiate class now...
        $object = new \ReflectionClass($class);
        if ($object->hasMethod('__construct')) {
            $dependencies = $this->dependencies_factory($library);
            $dependencies[]['requested_by'] = $library;
            $object = $object->newInstanceArgs($dependencies);
        } else {
            $object = $object->newInstanceWithoutConstructor();
        }

        // Do we have instruction to be instantiated once?
        if ($info['instantiation'] === 'once') {
            $this->cache[$library] = $object;
        }

        return $object;
    }

    /**
     * Construct dependencies from the list. Will return an array of objects.
     * --
     * @param  string $library
     * --
     * @return array
     */
    public function dependencies_factory($library)
    {
        $result = [];

        $info = $this->get_details($library);
        if (!$info) {
            throw new \Mysli\Librarian\DependencyException(
                "Cannot get details for: '{$library}'.",
                10
            );
        }
        $dependencies = $info['depends_on'];

        foreach ($dependencies as $dependency => $version) {
            $dependency_resovled = $this->resolve($dependency, 'enabled');

            // Check if we have match
            if (!$dependency_resovled) {
                throw new \Mysli\Librarian\DependencyException(
                    "Cannot get dependency: '{$dependency}' version: '{$version}'.",
                    20
                );
            }
            // Check if is enabled
            if (!$this->is_enabled($dependency_resovled)) {
                throw new \Mysli\Librarian\DependencyException(
                    "Dependency is not enabled: '{$dependency}' cannot proceed.",
                    30
                );
            }
            // Check if version is OK
            $resolved_version = $this->get_details($dependency_resovled)['version'];
            if (!\Int::compare_versions($resolved_version, $version)) {
                throw new \Mysli\Librarian\DependencyException(
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
     * Get and construct the setup file for particular library.
     * --
     * @param  string $library
     * --
     * @return mixed  Object (setup) or false
     */
    public function construct_setup($library)
    {
        $library_vendor = explode('/', $library)[0];
        $library_name   = explode('/', $library)[1];

        // Resolve class name with namespace!
        $setup_class_name = \Str::to_camelcase($library_vendor) .
                            '\\' .
                            \Str::to_camelcase($library_name) .
                            '\\' .
                            'Setup';

        // Does setup file exists
        if (!file_exists(libpath(ds($library, 'setup.php')))) {
            return false;
        }

        // $this->log->info("Setup was found for '{$library}'.", __FILE__, __LINE__);

        if (!class_exists($setup_class_name, false)) {
            include(libpath(ds($library, 'setup.php')));
            if (!class_exists($setup_class_name, false)) {
                throw new \Core\DataException(
                    "Class not found '{$setup_class_name}'."
                );
            }
        }

        // Construct and return
        $object = new \ReflectionClass($setup_class_name);
        if ($object->hasMethod('__construct')) {
            $dependencies = $this->dependencies_factory($library);
            $dependencies[]['requested_by'] = $library;
            return $object->newInstanceArgs($dependencies);
        } else {
            return $object->newInstanceWithoutConstructor();
        }
    }

    /**
     * This is (in core registered) autoloader.
     * --
     * @param  string $class
     * --
     * @throws \Mysli\Librarian\LibraryException If trying to load sub class
     *         of library which is not enabled.
     * --
     * @return void
     */
    public function autoloader($class)
    {
        // Perhaps it's a sub-class of a library? (\Mysli\Library\SubClass)
        if (substr_count($class, '\\') > 1) {

            // Create path e.g.: mysli/library/sub_class from class name
            $path = $this->ns_to_lib($class);
            // Create an array of path segments
            $segments = explode('/', $path);
            // The actual library name consist of first two segments
            $library = $segments[0] . '/'  . $segments[1];

            // The library must be enabled...
            if (!$this->is_enabled($library)) {
                throw new LibraryException(
                    "Cannot load class: `{$class}`, ".
                    "library is not enabled: `{$library}`.",
                    1
                );
            }

            // Are we dealing with exception?
            if (substr($class, -9, 9) === 'Exception') {
                $file = array_pop($segments);
                $file = substr($file, 0, -10);
                $path = libpath(implode('/', $segments) . '/exceptions/' . $file . '.php');
            } else {
                $path = libpath($path . '.php');
            }

            // We don't have file?
            if (!file_exists($path)) {
                throw new \Core\FileSystemException(
                    "Cannot find file for class: `{$class}` in: `{$path}`.",
                    1
                );
            }

            // All is ok, include the file.
            include $path;
        } else {
            $library = $this->ns_to_lib($class);
            $this->load($library);
        }
    }

    /**
     * Load library's main class.
     * --
     * @param  string  $library
     * @param  boolean $force   Will load library even if it's disabled!
     * --
     * @return boolean
     */
    public function load($library, $force = false)
    {
        $class = $this->lib_to_ns($library);
        $library_segments = explode('/', $library);
        $library_base = $library_segments[0] . '/' . $library_segments[1];

        if (class_exists($class, false)) { return true; }

        if (!$this->is_enabled($library_base) && !$force) {
            // $this->log->info(
            //     "Cannot load the class: '{$class}', " .
            //     "because the library is not enabled.",
            //     __FILE__, __LINE__
            // );
            return false;
        }

        if (count($library_segments) === 2) {
            $filename = libpath(ds($library, explode('/', $library)[1] . '.php'));
        } else {
            $filename = libpath(ds($library . '.php'));
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
     * Call library's method. Will automaticall load and construct it if needed,
     * --
     * @param  string $library
     * @param  string $method
     * @param  array  $params
     * --
     * @return mixed  Result of the execution.
     */
    public function call($library, $method, array $params = array())
    {
        $object = $this->factory($library);

        if (!is_object($object)) {
            throw new \Core\ValueException(
                "Could not construct the library: '{$library}'.",
                10
            );
        }

        if (!method_exists($object, $method)) {
            throw new \Core\ValueException(
                "Required method doesn't exists: '{$method}' for '{$library}'.",
                20
            );
        }

        return call_user_func_array([$object, $method], $params);
    }

    /**
     * Take library name (vendor/lib) and return namespace + class (Vendor/Class).
     * --
     * @param  string $library
     * --
     * @return string
     */
    public function lib_to_ns($library)
    {
        if (strpos($library, '/') === false) {
            return $library;
        }

        $class = \Str::to_camelcase($library);
        $class = str_replace('/', '\\', $class);

        return $class;
    }

    /**
     * Take the namespaced class name (Vendor/Class)
     * and return library name (vendor/lib).
     * --
     * @param  string $class
     * --
     * @return string
     */
    public function ns_to_lib($class)
    {
        if (strpos($class, '\\') === false) {
            return $class;
        }

        $library = \Str::to_underscore($class);
        $library = strtolower($library);
        $library = str_replace('\\', '/', $library);

        return $library;
    }

    /**
     * Check if particular library is enabled.
     * --
     * @param  string  $library
     * --
     * @return boolean
     */
    public function is_enabled($library)
    {
        return isset($this->enabled[$library]);
    }

    /**
     * This will get all the dependencies of provided library.
     * If you set $deep to true, it will resolve deeper relationships,
     * e.g. dependencies of dependencies.
     * --
     * @param  string  $library
     * @param  boolean $deep
     * --
     * @return array
     */
    public function get_dependencies($library, $deep = false)
    {
        $details = $this->get_details($library);
        if (!is_array($details) || empty($details)) {
            throw new \Core\DataException(
                "Could not get details for '{$library}'."
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
                    $dependencies = \Arr::merge(
                        $this->get_dependencies($dependency_resovled, true),
                        $dependencies
                    );
                }
            }
        }

        return $dependencies;
    }

    /**
     * This will get all the dependees (the libraries which requires provided
     * library, e.g. are dependent on it!)
     * If you set $deep to true, it will resolve deeper relationships,
     * e.g. dependees of dependees.
     * --
     * @param  string  $library
     * @param  boolean $deep
     * --
     * @return array
     */
    public function get_dependees($library, $deep = false)
    {
        $details = $this->get_details($library);
        if (!is_array($details) || empty($details)) {
            throw new \Core\DataException(
                "Could not get details for '{$library}'."
            );
        }

        if (!$deep) return $details['required_by'];

        $dependees = [];

        foreach ($details['required_by'] as $dependee) {
            $dependees[] = $dependee;
            $dependees = \Arr::merge(
                $this->get_dependees($dependee, true),
                $dependees
            );
        }

        return $dependees;
    }

    /**
     * Will disable particular library. Please note that this won't resolve
     * dependencies, you must do that manually.
     * This also won't call the setup automatically!
     * --
     * @param  string  $library
     * --
     * @return boolean
     */
    public function disable($library)
    {
        // Is enabled at all?
        if (!$this->is_enabled($library)) {
            throw new \Core\ValueException(
                "Cannot disable library: '{$library}' it's not enabled."
            );
        }

        $info = $this->get_details($library);

        // Is required by other libraries?
        if (!empty($info['required_by'])) {
            // $this->log->info(
            //     "Will disable library: '{$library}' ".
            //     "despite the fact that it's required by: " .
            //     dump_r($info['required_by']),
            //     __FILE__, __LINE__
            // );
        }

        // Remove itself from required_by
        foreach ($info['depends_on'] as $dependency => $version) {
            $dependency = $this->resolve($dependency, 'enabled');
            if ($dependency) {
                if (in_array($library, $this->enabled[$dependency]['required_by'])) {
                    unset(
                        $this->enabled[$dependency]['required_by'][
                            array_search(
                                $library,
                                $this->enabled[$dependency]['required_by'])
                        ]
                    );
                }
            }
        }

        // Remove the main key
        if (isset($this->enabled[$library])) {
            unset($this->enabled[$library]);
        }

        // Save changes
        return $this->registry_save();
    }

    /**
     * Will enable particular library. Please note that this won't resolve
     * dependencies, you must do that manually.
     * This also won't call the setup automatically!
     * --
     * @param  string $library
     * --
     * @return boolean
     */
    public function enable($library)
    {
        // Resolve the path
        $library_path   = libpath($library);
        $library_vendor = explode('/', $library)[0];
        $library_name   = explode('/', $library)[1];

        // Check if main class file exists
        if (!file_exists(ds($library_path, $library_name.'.php'))) {
            throw new \Core\FileSystemException(
                "Cannot find main library's class '{$library_name}.php' ".
                "in '{$library_path}'."
            );
        }

        // Get info!
        $info = $this->get_details($library);

        // Add new required_by key
        foreach ($info['depends_on'] as $dependency => $version) {
            $dependency = $this->resolve($dependency, 'enabled');
            if ($dependency) {
                $this->enabled[$dependency]['required_by'][] = $library;
            }
        }

        // Add library's details to the register
        $info['required_by'] = [];
        $this->enabled[$library] = $info;

        return $this->registry_save();
    }

    /**
     * Save changes (when enabling / disabling the library) to the registry file.
     * --
     * @return boolean
     */
    protected function registry_save()
    {
        return file_put_contents($this->filename, json_encode($this->enabled));
    }
}
