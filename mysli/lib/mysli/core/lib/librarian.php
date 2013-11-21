<?php

namespace Mysli\Core\Lib;

class Librarian
{
    // All the enabled libraries (loaded from init($filename))
    private static $enabled = [];
    // All the disabled libraries (cached for multiple calls)
    private static $disabled = [];
    // Libraries filename (from where list of enabled libraries is loaded and
    // where all the modifications are saved)
    private static $filename  = '';
    // Constrcuted libraries // kind of a like registry
    private static $cache     = [];

    /**
     * Init the librarian class. Will accept filename of
     * of registry file, containing currently enabled libraries.
     * --
     * @param  string $filename
     * --
     * @throws \Mysli\Core\FileSystemException
     * --
     * @return boolean
     */
    public static function init($filename)
    {
        if (file_exists($filename)) {
            self::$filename = $filename;
            self::$enabled = json_decode(file_get_contents($filename), true);
            if (!is_array(self::$enabled)) {
                throw new \Mysli\Core\DataException(
                    'Invalid libraries registry file.'
                );
            } else {
                return true;
            }
        } else {
            throw new \Mysli\Core\FileSystemException(
                "Cannot find the libraries registry file: `{$filename}`."
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
    public static function get_enabled($details = false)
    {
        if (!$details) {
            return array_keys(self::$enabled);
        } else {
            return self::$enabled;
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
    public static function get_disabled($details = false)
    {
        if (!empty(self::$disabled)) {
            if ($details) {
                if (is_array(Arr::first(self::$disabled))) {
                    return self::$disabled;
                }
            } else {
                return array_keys(self::$disabled);
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

                if (self::is_enabled($library)) continue;

                $disabled[$library] = true;

                if (!$details) continue;

                $disabled[$library] = self::get_details($library);
            }
        }

        self::$disabled = $disabled;
        return !$details ? array_keys(self::$disabled) : self::$disabled;
    }

    /**
     * Get details for particular (either enabled or disabled) library.
     * --
     * @param  string $library
     * --
     * @return array
     */
    public static function get_details($library)
    {
        if (self::is_enabled($library)) {
            return self::$enabled[$library];
        }

        // Disabled!
        $meta_file = libpath(ds($library, 'meta.json'));
        if (!file_exists($meta_file)) {
            throw new \Mysli\Core\FileSystemException(
                "Cannot find `meta.json` file for `{$library}`"
            );
        }

        $meta = json_decode(file_get_contents($meta_file), true);
        if (!is_array($meta)
            || !isset($meta['library'])
            || $meta['library'] !== $library
        ) {
            throw new \Mysli\Core\DataException(
                "Meta file for `{$library}` seems to be invalid: " .
                dump_r($meta)
            );
        }

        return $meta;
    }

    /**
     * Accept partial name (e.g. * /lib_name and return vendor/lib_name).
     * You can limit results to only enabled or disabled libraries (string).
     * This will stop on first match, if you want to get all libraries that
     * match the name, use resolve_all method.
     * --
     * This can also be used to simply check if particular library exists at all.
     * You can use vendor/lib for this purpose.
     * --
     * @param  string $library
     * @param  string $limit_to disabled | enabled | null (no limit)
     * --
     * @return mixed  String (lib full name || false if not found)
     */
    public static function resolve($library, $limit_to = null)
    {
        // Is regex?
        if (strpos($library, '*') !== false) {
            $library_regex =
                '/' .
                str_replace(['*', '/'], ['.*?', '\/'], $library) .
                '/i';
        }
        else {
            $library_regex = false;
        }

        // Enabled?
        if ($limit_to === null || $limit_to === 'enabled') {
            // No regular expression + enabled
            if (!$library_regex) {
                if (self::is_enabled($library)) return $library;
            }
            else {
                // Regular expression + enabled
                foreach (self::$enabled as $library => $data) {
                    if (preg_match($library_regex, $library)) return $library;
                }
            }
        }

        // Disabled?
        if ($limit_to === null || $limit_to === 'disabled') {
            self::get_disabled(false);
            // No regular expression + disabled
            if (!$library_regex) {
                if (isset(self::$disabled[$library])) return $library;
            }
            else {
                // Regular expression + disabled
                foreach (self::$disabled as $library => $data) {
                    if (preg_match($library_regex, $library)) return $library;
                }
            }
        }

        return false;
    }

    /**
     * Accept partial name (e.g. * /lib_name and return vendor/lib_name).
     * You can limit results to only enabled or disabled libraries (string).
     * This will return an array with all matches in no particular order.
     * --
     * @param  string $library
     * @param  string $limit_to disabled | enabled | null (no limit)
     * --
     * @return array  Empty if not matches.
     */
    public static function resolve_all($library, $limit_to = null)
    {
        $stack = [];

        // Is regex?
        if (strpos($library, '*') !== false) {
            $library_regex =
                '/' .
                str_replace(['*', '/'], ['.*?', '\/'], $library) .
                '/i';
        }
        else {
            $library_regex = false;
        }

        // Enabled?
        if ($limit_to === null || $limit_to === 'enabled') {
            // No regular expression + enabled
            if (!$library_regex) {
                if (self::is_enabled($library)) $stack[] = $library;
            }
            else {
                // Regular expression + enabled
                foreach (self::$enabled as $library => $data) {
                    if (preg_match($library_regex, $library)) $stack[] = $library;
                }
            }
        }

        if ($limit_to === null || $limit_to === 'disabled') {
            self::get_disabled(false);
            // No regular expression + disabled
            if (!$library_regex) {
                    if (isset(self::$disabled[$library])) $stack[] = $library;
            }
            else {
                // Regular expression + disabled
                foreach (self::$disabled as $library => $data) {
                    if (preg_match($library_regex, $library)) $stack[] = $library;
                }
            }
        }

        return $stack;
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
    public static function factory($library)
    {
        // Check if is enabled?
        if (!self::is_enabled($library)) {
            throw new \Mysli\Core\ValueException(
                "The library is not enabled: `{$library}`."
            );
        }

        // Check if we have it cached...
        if (isset(self::$cache[$library]) && is_object(self::$cache[$library])) {
            Log::info(
                "The library `{$library}` was found in cache!",
                __FILE__, __LINE__
            );
            return self::$cache[$library];
        }

        // Get library info
        $info = self::get_details($library);

        // Do we have the index?
        if (!isset($info['instantiation'])) {
            throw new \Mysli\Core\DataException(
                "The `instantiation` key is missing in meta for: `{$library}`."
            );
        }

        // Check the instantiation instructions
        if ($info['instantiation'] === 'never') {
            Log::info(
                "The library `{$library}` will not be auto instantiated. " .
                'The `instantiation` is set to `never`.',
                __FILE__, __LINE__
            );
            return false;
        }

        // Check if we have class and if not, fetch it ...
        $class = self::lib_to_ns($library);

        if (!class_exists($class, false)) {
            if (!self::load($library)) {
                return false;
            }
        }

        // Check if we have manual instruction for instantiation
        if ($info['instantiation'] === 'manual') {
            Log::info(
                "The library `{$library}` should be manually instantiated. ".
                'The `instantiation` is set to `manual`. ' .
                'Will return a class name.',
                __FILE__, __LINE__
            );
            return $class;
        }

        // Instantiate class now...
        $object = new $class(self::dependencies_factory($library));

        // Do we have instruction to be instantiated once?
        if ($info['instantiation'] === 'once') {
            self::$cache[$library] = $object;
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
    public static function dependencies_factory($library)
    {
        $result = [];

        $info = self::get_details($library);
        if (!$info) {
            throw new \Mysli\Core\DataException(
                "Cannot get details for: `{$library}`."
            );
        }
        $dependencies = $info['depends_on'];

        foreach ($dependencies as $dependency => $version) {
            $dependency_resovled = self::resolve($dependency, 'enabled');

            // Check if we have match
            if (!$dependency_resovled) {
                throw new \Mysli\Core\FileSystemException(
                    "Cannot get dependency: `{$dependency}` version: `{$version}`."
                );
            }
            // Check if is enabled
            if (!self::is_enabled($dependency_resovled)) {
                throw new \Mysli\Core\ValueException(
                    "Dependency is not enabled: `{$dependency}` cannot proceed."
                );
            }
            // Check if version is OK
            $resolved_version = self::get_details($dependency_resovled)['version'];
            if (!Int::compare_versions($resolved_version, $version)) {
                throw new \Mysli\Core\ValueException(
                    "Dependency version is not correct. ".
                    "Version: `{$resolved_version}` required: `{$version}`."
                );
            }
            $result[explode('/', $dependency_resovled)[1]] = self::factory($dependency_resovled);
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
    public static function construct_setup($library)
    {
        $library_vendor = explode('/', $library)[0];
        $library_name   = explode('/', $library)[1];

        // Resolve class name with namespace!
        $setup_class_name = Str::to_camelcase($library_vendor) .
                            CHAR_BACKSLASH .
                            Str::to_camelcase($library_name) .
                            CHAR_BACKSLASH .
                            'Setup';

        // Does setup file exists
        if (!file_exists(libpath(ds($library, 'setup.php')))) {
            return false;
        }

        Log::info("Setup was found for `{$library}`.", __FILE__, __LINE__);

        if (!class_exists($setup_class_name, false)) {
            include(libpath(ds($library, 'setup.php')));
            if (!class_exists($setup_class_name, false)) {
                throw new \Mysli\Core\FileSystemException(
                    "Class not found `{$setup_class_name}`."
                );
            }
        }

        // Construct and return
        $dependencies = self::dependencies_factory($library);
        return new $setup_class_name($dependencies);
    }

    /**
     * This is (in core registered) autoloader.
     * --
     * @param  string $class
     * --
     * @return void
     */
    public static function autoloader($class)
    {
        $library = self::ns_to_lib($class);
        self::load($library);
    }

    /**
     * Load library's main class.
     * --
     * @param  string  $library
     * @param  boolean $force   Will load library even if it's disabled!
     * --
     * @return boolean
     */
    public static function load($library, $force = false)
    {
        $class = self::lib_to_ns($library);

        if (class_exists($class, false)) { return true; }

        if (!self::is_enabled($library) && !$force) {
            Log::info(
                "Cannot load the class: `{$class}`, ".
                "because the library is not enabled.",
                __FILE__, __LINE__
            );
            return false;
        }

        $filename = libpath(ds($library, explode('/', $library)[1] . '.php'));

        if (!file_exists($filename)) {
            Log::info(
                "File not found: `{$filename}`.",
                __FILE__, __LINE__
            );
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
    public static function call($library, $method, array $params = array())
    {
        $object = self::factory($library);

        if (!is_object($object)) {
            throw new \Mysli\Core\ValueException(
                'Could not construct the library: `' . $library . '`.'
            );
        }

        if (!method_exists($object, $method)) {
            throw new \Mysli\Core\ValueException(
                "Required method doesn't exists: `{$method}` for `{$library}`."
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
    public static function lib_to_ns($library)
    {
        if (strpos($library, '/') === false) {
            return $library;
        }

        $class = Str::to_camelcase($library);
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
    public static function ns_to_lib($class)
    {
        if (strpos($class, '\\') === false) {
            return $class;
        }

        $library = Str::to_underscore($class);
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
    public static function is_enabled($library)
    {
        return isset(self::$enabled[$library]);
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
    public static function get_dependencies($library, $deep = false)
    {
        $details = self::get_details($library);
        if (!is_array($details) || empty($details)) {
            throw new \Mysli\Core\DataException(
                "Could not get details for `{$library}`."
            );
        }

        $dependencies = [
            'enabled'  => [],
            'disabled' => [],
            'missing'  => []
        ];

        foreach ($details['depends_on'] as $dependency => $version) {
            $dependency_resovled = self::resolve($dependency);
            if (!$dependency_resovled) {
                $dependencies['missing'][$dependency] = $version;
            }
            else {
                if (self::is_enabled($dependency_resovled)) {
                    $dependencies['enabled'][$dependency_resovled] = $version;
                } else {
                    $dependencies['disabled'][$dependency_resovled] = $version;
                }
            }
        }

        if (!$deep) return $dependencies;

        foreach ($dependencies['disabled'] as $dependency => $version) {
            $dependency_resovled = self::resolve($dependency);
            if (!$dependency_resovled) {
                $dependencies['missing'] = array_merge(
                    [$dependency => $version],
                    $dependencies['missing']
                );
            } else {
                if (self::is_enabled($dependency_resovled)) {
                    $dependencies['enabled'][$dependency_resovled] = $version;
                } else {
                    $dependencies = Arr::merge(
                        self::get_dependencies($dependency_resovled, true),
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
    public static function get_dependees($library, $deep = false)
    {
        $details = self::get_details($library);
        if (!is_array($details) || empty($details)) {
            throw new \Mysli\Core\DataException(
                "Could not get details for `{$library}`."
            );
        }

        if (!$deep) return $details['required_by'];

        $dependees = [];

        foreach ($details['required_by'] as $dependee) {
            $dependees[] = $dependee;
            $dependees = Arr::merge(
                self::get_dependees($dependee, true),
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
    public static function disable($library)
    {
        // Is enabled at all?
        if (!self::is_enabled($library)) {
            throw new \Mysli\Core\ValueException(
                "Cannot disable library: `{$library}` it's not enabled."
            );
        }

        $info = self::get_details($library);

        // Is required by other libraries?
        if (!empty($info['required_by'])) {
            Log::info(
                "Will disable library: `{$library}` ".
                "despite the fact that it's required by: " .
                dump_r($info['required_by']),
                __FILE__, __LINE__
            );
        }

        // Remove itself from required_by
        foreach ($info['depends_on'] as $dependency => $version) {
            $dependency = self::resolve($dependency, 'enabled');
            if ($dependency) {
                if (in_array($library, self::$enabled[$dependency]['required_by'])) {
                    unset(
                        self::$enabled[$dependency]['required_by'][
                            array_search(
                                $library,
                                self::$enabled[$dependency]['required_by'])
                        ]
                    );
                }
            }
        }

        // Remove the main key
        if (isset(self::$enabled[$library])) {
            unset(self::$enabled[$library]);
        }

        // Save changes
        return self::registry_save();
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
    public static function enable($library)
    {
        // Resolve the path
        $library_path   = libpath($library);
        $library_vendor = explode('/', $library)[0];
        $library_name   = explode('/', $library)[1];

        // Check if main class file exists
        if (!file_exists(ds($library_path, $library_name.'.php'))) {
            throw new \Mysli\Core\FileSystemException(
                "Cannot find main library's class `{$library_name}.php` ".
                "in `{$library_path}`."
            );
        }

        // Get info!
        $info = self::get_details($library);

        // Add new required_by key
        foreach ($info['depends_on'] as $dependency => $version) {
            $dependency = self::resolve($dependency, 'enabled');
            if ($dependency) {
                self::$enabled[$dependency]['required_by'][] = $library;
            }
        }

        // Add library's details to the register
        $info['required_by'] = [];
        self::$enabled[$library] = $info;

        return self::registry_save();
    }

    /**
     * Save changes (when enabling / disabling the library) to the registry file.
     * --
     * @return boolean
     */
    protected static function registry_save()
    {
        return file_put_contents(self::$filename, json_encode(self::$enabled));
    }
}
