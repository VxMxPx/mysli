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

        foreach ($vendors as $vendor) {
            if (substr($vendor, 0, 1) === '.') { continue; }
            $vendor_libraries = scandir(libpath($vendor));
            foreach ($vendor_libraries as $library) {
                if (substr($library, 0, 1) === '.') { continue; }
                $library_key = $vendor . '/' . $library;
                if (!self::is_enabled($library_key)) {
                    $meta_path = libpath(ds($library_key, 'meta.json'));
                    if (file_exists($meta_path)) {
                        $libraries[$library_key] = [];
                        if ($detailed) {
                            $libraries[$library_key] = self::get_details($library_key);
                        }
                    }
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
     * @param  string  $library Can be * /core, etc...
     * @param  mixed   $version If not float, it will be converted
     * --
     * @return boolean
     */
    public static function is_satisfied($library, $version)
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
                        return $version >= floatval($enabled_lib['version']);
                        break;
                    case '<=':
                        return $version <= floatval($enabled_lib['version']);
                        break;
                    case '=':
                        return $version === floatval($enabled_lib['version']);
                        break;
                    case '<':
                        return $version < floatval($enabled_lib['version']);
                        break;
                    case '>':
                        return $version > floatval($enabled_lib['version']);
                        break;
                    default:
                        return false;
                }
            }
        }
        return false;
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
            if (!self::is_satisfied($depends, $version)) {
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
     * @return mixed  List of dependences, or true if nothing is dependent.
     */
    public static function can_disable($library)
    {
        return empty(self::$libraries[$library]['required_by'])
                ? true
                : self::$libraries[$library]['required_by'];
    }

    /**
     * Will enable particular library.
     * If the $library is an array,
     * then all libraries on the list will be enabled.
     * --
     * @param  mixed $library String or array.
     * --
     * @return integer        Number of enabled libraries.
     */
    public static function enable($library)
    {
        if (is_array($library)) {
            $num = 0;
            foreach ($library as $library_item) {
                $num += self::enable($library_item);
            }
            return $num;
        }

        // Resolve the path
        $library_path   = libpath($library);
        $library_vendor = explode('/', $library)[0];
        $library_name   = explode('/', $library)[1];

        // Check if main class file exists
        if (!file_exists(ds($library_path, $library_name.'.php'))) {
            Log::warn("Cannot find main library's class `{$library_name}.php` in `{$library_path}`.", __FILE__, __LINE__);
            return 0;
        }

        // Does setup file exists
        if (file_exists(ds($library_path, 'setup.php'))) {
            Log::info("Setup was found for `{$library}`.", __FILE__, __LINE__);
            // Include it
            include(ds($library_path, 'setup.php'));
            // Resolve class name with namespace!
            $setup_class_name = Str::to_camelcase($library_vendor) .
                                CHAR_BACKSLASH .
                                Str::to_camelcase($library_name) .
                                CHAR_BACKSLASH .
                                'Setup';
            // Construct it
            $setup = new $setup_class_name();
            // Execute before_enable method if result is true, then continue
            if (method_exists($setup, 'before_enable')) {
                if (!call_user_func([$setup, 'before_enable'])) {
                    Log::warn("Method before_enable was unsuccessful for: `{$library}`.", __FILE__, __LINE__);
                    return 0;
                }
            }
        }

        // Get details
        // Add new required_by key
        // Add library's details to the register
        //
        // Check dependencies and add itself to their required_by array
        //
        // Save the register

        // Do we have setup object?
            // Execute after_enable method
    }

    /**
     * Will disable particular library.
     * If the $library is an array,
     * then all libraries on the list will be disabled.
     * --
     * @param  mixed $library String or array.
     * --
     * @return integer        Number of disabled libraries.
     */
    public static function disable($library)
    {

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
     * @return object || false
     */
    public static function construct($library)
    {
        $class = self::id_to_ns($library);

        if (!class_exists($class, false)) {
            if (!self::autoloader($class)) {
                return false;
            }
            self::$cache[$library] = new $class();
        }

        return self::$cache[$library];
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

        if (!isset(self::$cache[$lib])) {
            if (!self::construct($lib)) {
                Log::warn('Cannot call the function: ' . print_r($func, true), __FILE__, __LINE__);
                return false;
            }
        }

        if (method_exists(self::$cache[$lib], $func[1])) {
            return call_user_func_array([self::$cache[$lib], $func[1]], $params);
        }
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
        if (self::is_enabled($id)) {
            $filename = libpath($id . '/' . substr($id, strrpos($id, '/')) . '.php');
            if (file_exists($filename)) {
                include $filename;
                return true;
            } else {
                Log::warn('File not found: `' . $filename . '`.', __FILE__, __LINE__);
                return false;
            }
        } else {
            Log::warn("Cannot autoload the class: `{$class}`, because the library is not enabled.", __FILE__, __LINE__);
            return false;
        }
    }
}