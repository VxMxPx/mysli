<?php

namespace Mysli\Core\Lib;

class Librarian
{
    // All the libraries available (loaded from init($filename))
    private static $libraries = [];
    // Libraries filename
    private static $filename  = '';
    // Constrcuted libraries
    private static $cache     = [];

    /**
     * Init the librarian class. Will accept filename of
     * of registry file, containing currently enabled libraries.
     * --
     * @param  string $filename
     * --
     * @return void
     */
    public static function init($filename)
    {
        self::$filename = $filename;
        if (file_exists($filename)) {
            self::$libraries = json_decode(file_get_contents($filename), true);
        }
    }

    /**
     * Return the list of enabled libraries.
     * --
     * @return array
     */
    public static function get_enabled()
    {
        return self::$libraries;
    }

    /**
     * Return the list of disabled libraries.
     * --
     * @param  boolean $details Whether we need detailed list (will load each
     *                          library's meta).
     * --
     * @return array
     */
    public static function get_disabled($detailed=false)
    {
        $vendors = scandir(libpath());
        $libraries = [];

        foreach ($vendors as $vendor)
        {
            if (substr($vendor, 0, 1) === '.') { continue; }
            $vendor_libraries = scandir(libpath($vendor));

            foreach ($vendor_libraries as $library)
            {
                if (substr($library, 0, 1) === '.') { continue; }
                $library_key = $vendor . '/' . $library;

                if (self::is_enabled($library_key)) { continue; }
                $meta_path = libpath(ds($library_key, 'meta.json'));

                if (!file_exists($meta_path)) { continue; }
                $libraries[$library_key] = [];

                if ($detailed) {
                    $libraries[$library_key] = self::get_details($library_key);
                }
            }
        }

        return $libraries;
    }

    /**
     * Get Details about particular (can be enabled or disabled) library.
     * --
     * @param  string $library
     * --
     * @return array
     */
    public static function get_details($library)
    {
        if (self::is_enabled($library)) {
            return self::$libraries[$library];
        } else {
            $meta_path = libpath(ds($library, 'meta.json'));
            if (file_exists($meta_path)) {
                return json_decode(file_get_contents($meta_path), true);
            } else {
                Log::warn("Cannot find file: `{$meta_path}`.", __FILE__, __LINE__);
            }
        }
        return [];
    }

    /**
     * Is particular dependency satisfied (e.g. is enabled and version match)
     * If yes it will return string will full dependency's name otherwise false.
     * This can be used to get (enabled) library full name. Example:
     * * /core => mysli/core.
     * --
     * @param  string  $library Can be * /core, etc...
     * @param  mixed   $version If not float, it will be converted
     * --
     * @return string  || boolean
     */
    public static function get_satisfied($library, $version)
    {
        // Convert to proper regular expression
        $library = str_replace(['*', '/'], ['.*?', '\/'], $library);
        $library = '/' . $library . '/i';

        // Resolve version
        $version = explode(' ', $version);
        $version_operator = trim($version[0]);
        $version = floatval(trim($version[1]));

        foreach (self::$libraries as $enabled_key => $enabled_lib) {
            if (preg_match($library, $enabled_key)) {
                switch ($version_operator) {
                    case '>=':
                        return $version >= floatval($enabled_lib['version'])
                                    ? $enabled_lib['library']
                                    : false;
                        break;
                    case '<=':
                        return $version <= floatval($enabled_lib['version'])
                                    ? $enabled_lib['library']
                                    : false;
                        break;
                    case '=':
                        return $version === floatval($enabled_lib['version'])
                                    ? $enabled_lib['library']
                                    : false;
                        break;
                    case '<':
                        return $version < floatval($enabled_lib['version'])
                                    ? $enabled_lib['library']
                                    : false;
                        break;
                    case '>':
                        return $version > floatval($enabled_lib['version'])
                                    ? $enabled_lib['library']
                                    : false;
                        break;
                    default:
                        return false;
                }
            }
        }
        return false;
    }

    /**
     * Construct all dependencies. Will return an array of objects.
     * --
     * @param  array $dependencies
     * --
     * @return array
     */
    public static function dependencies_factory(array $dependencies)
    {
        $result = [];

        foreach ($dependencies as $dependency => $version) {
            $name = substr($dependency, strpos($dependency, '/'));
            $sdep = self::get_satisfied($dependency, $version);
            if (!$sdep) {
                trigger_error(
                    "Cannot get dependency for: `{$dependency}` version: `{$version}`.",
                    E_USER_ERROR
                );
                return;
            }
            $result[$name] = self::construct($sdep);
        }
        return $result;
    }

    /**
     * Check if there are any dependencies that aren't satisfied, or if something
     * else is missing before the library can be enabled.
     * --
     * @param  string $library
     * --
     * @return mixed  List of missing things, or true if nothing is missing.
     */
    public static function can_enable($library)
    {
        $not_satisfied = [];
        $details = self::get_details($library);
        if (!isset($details['depends_on']) || !is_array($details['depends_on'])) {
            return [];
        }
        foreach ($details['depends_on'] as $depends => $version) {
            if (!self::get_satisfied($depends, $version)) {
                $not_satisfied[$depends] = $version;
            }
        }
        return $not_satisfied;
    }

    /**
     * Check if there's any library that's dependent on this one.
     * --
     * @param  string $library
     * --
     * @return mixed  List of dependencies, or true if nothing is dependent.
     */
    public static function can_disable($library)
    {
        return empty(self::$libraries[$library]['required_by'])
                ? true
                : self::$libraries[$library]['required_by'];
    }

    /**
     * Get and construct the setup file!
     * --
     * @param  string $library
     * @param  array  $dependencies
     * --
     * @return mixed  Object (setup) or false
     */
    public static function construct_setup($library, array $dependencies = array())
    {
        $library_path = libpath($library);
        $library_vendor = explode('/', $library)[0];
        $library_name   = explode('/', $library)[1];

        // Resolve class name with namespace!
        $setup_class_name = Str::to_camelcase($library_vendor) .
                            CHAR_BACKSLASH .
                            Str::to_camelcase($library_name) .
                            CHAR_BACKSLASH .
                            'Setup';

        // Does setup file exists
        if (!file_exists(ds($library_path, 'setup.php'))) {
            return false;
        }

        Log::info("Setup was found for `{$library}`.", __FILE__, __LINE__);

        if (!class_exists($setup_class_name, false)) {
            include(ds($library_path, 'setup.php'));
        }

        // Construct and return
        return new $setup_class_name($dependencies);
    }

    /**
     * Will enable particular library.
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

        $setup = false;

        // Check if main class file exists
        if (!file_exists(ds($library_path, $library_name.'.php'))) {
            Log::warn(
                "Cannot find main library's class `{$library_name}.php` ".
                "in `{$library_path}`.",
                __FILE__, __LINE__
            );
            return false;
        }

        // Get info!
        $info = self::get_details($library);

        // Get setup
        $dependencies_constructed = self::dependencies_factory($info['depends_on']);
        $setup = self::construct_setup($library, $dependencies_constructed);
        if ($setup) {
            // Execute before_enable method if result is true, then continue
            if (method_exists($setup, 'before_enable')) {
                if (!call_user_func([$setup, 'before_enable'])) {
                    Log::warn(
                        "Method before_enable was unsuccessful for: `{$library}`.",
                        __FILE__, __LINE__
                    );
                    return false;
                }
            }
        }

        // Add new required_by key
        foreach ($info['depends_on'] as $dependency => $version) {
            $sat = self::get_satisfied($dependency, $version);
            if ($sat) {
                self::$libraries[$dependency['library']]['required_by'][$library] = $version;
            }
        }

        // Add library's details to the register
        $info['required_by'] = [];
        self::$libraries[$library] = $info;

        self::registry_save();

        if ($setup) {
            if (method_exists($setup, 'after_enable')) {
                if (!call_user_func([$setup, 'after_enable'])) {
                    Log::warn(
                        "Method after_enable was unsuccessful for: `{$library}`.",
                        __FILE__, __LINE__
                    );
                }
            }
        }

        return true;
    }

    /**
     * Will save changes in registry.
     * --
     * @return boolean
     */
    private function registry_save()
    {
        return file_put_contents(self::$filename, json_encode(self::$libraries));
    }

    /**
     * Will disable particular library.
     * --
     * @param  string  $library
     * @param  boolean $force   Remove even if required by other libraries.
     * --
     * @return boolean
     */
    public static function disable($library, $force = false)
    {
        // Is enabled at all?
        if (!self::is_enabled($library)) {
            Log::warn(
                "Cannot disable library: `{$library}` it's not enabled.",
                __FILE__, __LINE__
            );
            return false;
        }

        $info = self::get_details($library);

        // Is required by other libraries?
        if (!empty($info['required_by'])) {
            if (!$force) {
                Log::warn(
                    "Cannot disable library: `{$library}`, it's required by: " .
                    dump_r($info['required_by']),
                    __FILE__, __LINE__
                );
                return false;
            } else {
                Log::info(
                    "Will disable library: `{$library}` ".
                    "despite the fact that it's required by: " .
                    dump_r($info['required_by']),
                    __FILE__, __LINE__
                );
            }
        }

        // Setup file exists, etc...
        $dependencies_constructed = self::dependencies_factory($info['depends_on']);
        $setup = self::construct_setup($library, $dependencies_constructed);
        if ($setup) {
            // Execute before_disable method if result is true, then continue
            if (method_exists($setup, 'before_disable')) {
                if (!call_user_func([$setup, 'before_disable'])) {
                    Log::warn(
                        "Method before_disable was unsuccessful for: `{$library}`.",
                        __FILE__, __LINE__
                    );
                    return false;
                }
            }
        }

        // Remove itself from required_by
        foreach ($info['depends_on'] as $dependency => $version) {
            $sat = self::get_satisfied($dependency, $version);
            if ($sat) {
                if (isset(self::$libraries[$dependency['library']]['required_by'][$library])) {
                    unset(self::$libraries[$dependency['library']]['required_by'][$library]);
                }
            }
        }

        // Remove the main key
        if (isset(self::$libraries[$library])) {
            unset(self::$libraries[$library]);
        }

        // Save changes
        self::registry_save();

        // Run after_disable
        if ($setup) {
            if (method_exists($setup, 'after_disable')) {
                if (!call_user_func([$setup, 'after_disable'])) {
                    Log::warn(
                        "Method after_disable was unsuccessful for: `{$library}`.",
                        __FILE__, __LINE__
                    );
                }
            }
        }

        return true;
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
        return isset(self::$libraries[$library]);
    }

    /**
     * Convert namespace to id.
     * Example: Mysli\Core => mysli/core
     *          Mysli\ManageUsers => mysli\manage_users
     * --
     * @param  string $ns
     * --
     * @return string
     */
    public static function ns_to_id($ns)
    {
        if (strpos($ns, '\\') === false) {
            return $ns;
        }

        $id = Str::to_underscore($ns);
        $id = strtolower($id);
        $id = str_replace('\\', '/', $id);

        return $id;
    }

    /**
     * Convert id to namespace.
     * Example: mysli/core => Mysli\Core
     *          mysli/manage_users => Mysli\ManageUsers
     * --
     * @param  string $id
     * --
     * @return string
     */
    public static function id_to_ns($id)
    {
        if (strpos($id, '/') === false) {
            return $id;
        }

        $ns = Str::to_camelcase($id);
        $ns = str_replace('/', '\\', $ns);

        return $ns;
    }

    /**
     * Will construct (if needed) particular library. This will auto-manage all
     * the dependencies needed.
     * Require library id!
     * --
     * @param  string $library
     * --
     * @return object || string || false
     */
    public static function construct($library)
    {
        // Check if is enabled?
        if (!self::is_enabled($library)) {
            Log::warn(
                "The library is not enabled: `{$library}`.",
                __FILE__, __LINE__
            );
            return false;
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
        $info = self::$libraries[$library];

        // Do we have the index?
        if (!isset($info['instantiation'])) {
            Log::warn(
                "The `instantiation` key is missing in meta for: `{$library}`.",
                __FILE__, __LINE__
            );
            return false;
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
        $class = self::id_to_ns($library);

        if (!class_exists($class, false)) {
            if (!self::autoloader($class)) {
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
        $object = new $class(self::dependencies_factory($info['depends_on']));

        // Do we have instruction to be instantiated once?
        if ($info['instantiation'] === 'once') {
            self::$cache[$library] = $object;
        }

        return $object;
    }

    /**
     * Call library's method. Will automatically construct it if needed.
     * --
     * @param  array $func    [Library, method]
     * @param  array $params  Parameters to be passed to the method.
     * --
     * @return mixed          Result of the execution.
     */
    public static function call($func, $params)
    {
        $lib = self::ns_to_id($func[0]);
        $obj = self::construct($lib);

        if (!is_object($obj)) {
            Log::warn(
                'The library cannot be constructed, not executed: ' .
                print_r($func, true),
                __FILE__, __LINE__
            );
            return false;
        }

        if (!method_exists($obj, $func[1])) {
            Log::warn(
                "Required method `{$func[1]}` not found in `{$lib}`.",
                __FILE__, __LINE__
            );
            return false;
        }

        return call_user_func_array([$obj, $func[1]], $params);
    }

    /**
     * Autoload class (if library is enabled).
     * --
     * @param  string $class
     * --
     * @return boolean
     */
    public static function autoloader($class)
    {
        if (class_exists($class, false)) { return true; }

        $id = self::ns_to_id($class);

        if (!self::is_enabled($id)) {
            Log::warn(
                "Cannot autoload the class: `{$class}`, ".
                "because the library is not enabled.",
                __FILE__, __LINE__
            );
            return false;
        }

        $filename = libpath($id . '/' . substr($id, strrpos($id, '/')) . '.php');

        if (!file_exists($filename)) {
            Log::warn("File not found: `{$filename}`.", __FILE__, __LINE__);
            return false;
        }

        include $filename;
        return true;
    }
}