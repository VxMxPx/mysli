<?php

/**
 * # Config
 *
 * Used to access and create configuration for packages.
 *
 * ## Usage
 *
 * Example, create configuration for a package:
 *
 *      $options = [
 *          'an_example' => ['string', 'example_value']
 *      ];
 *      $config = config::select('vendor.package');
 *      $config->init($options);
 *      $config->save();
 *
 * When initializing new options, as in example above, each value must be
 * an array, of which first element must be type, and second must be actual value.
 * When later a value is changed (set) type is not needed, but value needs
 * to be as it was specified when configuration was initialized.
 *
 * Configuration needs to be initialized only once,
 * usually when package is enabled.
 *
 * Access configuration:
 *
 *     $config = config::select('vendor.package');
 *     $config->get('an_example'); // example_value
 *
 * Or statically:
 *
 *     $example_value = config::select('vendor.package', 'an_example');
 *
 * Setting a value:
 *
 *     $c = config::select('vendor.package')
 *     $c->set('example_value', true);
 *
 * Adding a new configuration key:
 *
 *     $c = config::select('vendor.package')
 *     $c->add('example_value', 'boolean', true);
 *
 * To remove all configurations for a particular package `destroy` method
 * can be used:
 *
 *     config::select('vendor.package')->destroy();
 *
 * NOTE: This class is singleton, only one instance is allowed per PACKAGE.
 * Constructor is hence private, and config::select() must be used,
 * to get a (new) instance of configuration.
 */
namespace mysli\toolkit; class config
{
    const __use = '
        .{
            pkg,
            json,
            fs,
            fs.file -> file,
            log,
            type.arr -> arr,
            exception.config
        }
    ';

    /**
     * Currently selected package.
     * --
     * @var string
     */
    private $package;

    /**
     * Filename for currently selected package.
     * --
     * @var string
     */
    private $filename;

    /**
     * All values for currently selected package.
     * --
     * @var array
     */
    private $data  = [];

    /**
     * Initialize this configuration. In practice this should be called only
     * once for each package, --- when package is `enabled`.
     *
     * This will empty any data that's currently set, and replace it with new
     * configuration keys/types/values.
     * --
     * @param array $values
     *        Must be in format: [key => [type, value], key => [type, value]]
     * --
     * @throws \mysli\toolkit\exception\config 10 Invalid type/value format.
     * @throws \mysli\toolkit\exception\config 20 Value is of incorrect type.
     */
    function init(array $values)
    {
        $this->data = [];

        foreach ($values as $key => $opt)
        {
            if (!is_array($opt) || count($opt) !== 2)
                throw new exception\config(
                    "Invalid type/value format, expected [type, value] ".
                    "for: `{$key}`.", 10
                );

            list($type, $value) = $opt;

            if (!$this->validate_type($type, $value))
                throw new exception\config(
                    "Value is of incorrect type, expected: `{$type}`, got: `".
                    gettype($value)."`.", 20
                );

            $this->data[$key] = [$type, $value];
        }
    }

    /**
     * Get config element, by: sub.key.main
     * --
     * @param mixed $key
     *        String (sub.key) or array ([sub.key, sub.another_key]).
     *
     * @param mixed $default
     *        Default value if key not found.
     * --
     * @return mixed
     */
    function get($key, $default=null)
    {
        // If key is an array, do recursive search.
        if (is_array($key))
        {
            $values = [];

            foreach ($key as $val)
            {
                $values[] = $this->get($val, $default);
            }

            return $values;
        }

        if (isset($this->data[$key]))
        {
            // 1st element is returned, element 0, is data type.
            return $this->data[$key][1];
        }
        else
        {
            return $default;
        }
    }

    /**
     * Get type for particular key.
     * --
     * @param  string $key
     * --
     * @return string
     */
    function get_type($key)
    {
        if (isset($this->data[$key]))
            return $this->data[$key][0];
        else
            return null;
    }

    /**
     * Return all configurations for current package, as an array.
     * This will return types too!
     * --
     * @return array
     */
    function as_array()
    {
        return $this->data;
    }

    /**
     * Set value for key. Value must be of a specified type!
     * To keep changes, use $config->save();
     * --
     * @param string  $key sub.key
     * @param mixed   $value
     * @param boolean $overwrite_array
     *        When value is an array, weather to overwrite it, or merge it.
     * --
     * @throws \mysli\toolkit\exception\config 10 Key doesn't exists.
     * @throws \mysli\toolkit\exception\config 20 Value is of incorrect type.
     */
    function set($key, $value, $overwrite_array=false)
    {
        if (!isset($this->data[$key]))
            throw new exception\config(
                "Key doesn't exists, please use `add` if you'd like to add ".
                "a new key. Key: `{$key}`.", 10
            );

        $type = $this->data[$key][0];

        if (!$this->validate_type($type, $value))
            throw new exception\config(
                "Value is of incorrect type, expected: `{$type}`, got: `".
                gettype($value)."`.", 20
            );

        if ($type === 'array' && !$overwrite_array)
        {
            $value = arr::merge( $this->data[$key][1], $value );
        }

        $this->data[$key][1] = $value;
    }

    /**
     * Define a new config key. If you'd like to just set key, then use `set`.
     * To keep changes, use $config->save();
     * --
     * @param string $key
     * @param string $type
     * @param mixed  $value
     * --
     * @throws \mysli\toolkit\exception\config
     *         10 A configuration key already exists.
     *
     * @throws \mysli\toolkit\exception\config 20 Value is of incorrect type.
     */
    function add($key, $type, $value)
    {
        if (isset($this->data[$key]))
            throw new exception\config(
                "A configuration key already exists: `{$key}`.", 10
            );

        if (!$this->validate_type($type, $value))
            throw new exception\config(
                "Value is of incorrect type, expected: `{$type}`, got: `".
                gettype($value)."`.", 20
            );

        $this->data[$key] = [$type, $value];
    }

    /**
     * Save configuration to file.
     * --
     * @return boolean
     */
    function save()
    {
        return json::encode_file($this->filename, $this->data);
    }

    /**
     * Delete configuration file entirely.
     * --
     * @return boolean
     */
    function destroy()
    {
        $this->data = [];
        unset(self::$registry[$this->package]);

        return file::exists($this->filename)
            ? file::remove($this->filename)
            : true;
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Instace of config object.
     * --
     * @throws mysli\toolkit\exception\config
     *         10 Invalid package name for config.
     * --
     * @param string $package vendor.package or a namespace.
     */
    private function __construct($package)
    {
        $this->package = self::ns_to_pkg($package);

        log::debug("Create for package: `{$package}`.", __CLASS__);

        if (!$this->package)
            throw new exception\config("Invalid package name for config.", 10);

        $this->filename = fs::cfgpath('pkg', $this->package.'.json');

        // If we have file, then load contents...
        if (file::exists($this->filename))
            $this->data = json::decode_file($this->filename, true);
    }

    /**
     * Check if value is of required type.
     * --
     * @param  string $type
     * @param  mixed  $value
     * --
     * @throws \mysli\toolkit\exception\config 10 Unsupported type.
     * --
     * @return boolean
     */
    private function validate_type($type, $value)
    {
        if (is_null($value))
            return true;

        switch ($type)
        {
            case 'boolean':
                return is_bool($value);

            case 'string':
                return is_string($value);

            case 'integer':
                return is_integer($value);

            case 'float':
                return is_float($value);

            case 'numeric':
                return is_float($value) || is_integer($value);

            case 'array':
                return is_array($value);

            default:
                throw new exception\config("Unsupported type: `{$type}`", 10);
        }
    }

    /*
    --- Static -----------------------------------------------------------------
     */

    /**
     * Contains so far constructed objects, so that config object is constructed
     * only once for each package, when using `::select`.
     * --
     * @var array
     */
    static private $registry = [];

    /**
     * Get an instance of config or value from it.
     * --
     * @param string $package Use vendor.package or full namespace.
     * @param mixed  $key
     * @param mixed  $default
     * --
     * @throws mysli\toolkit\exception\config 10 Invalid package.
     * --
     * @return \mysli\toolkit\config or a mixed value
     */
    static function select($package, $key=false, $default=null)
    {
        $package = self::ns_to_pkg($package);

        if (!$package)
            throw new exception\config("Invalid package.", 10);

        if (!isset(self::$registry[$package]))
            self::$registry[$package] = new self($package);

        $config = self::$registry[$package];

        return $key
            ? $config->get($key, $default)
            : $config;
    }

    /**
     * Get list of all packages that configuration is available for.
     * --
     * @return array
     */
    static function get_list()
    {
        $packages = [];

        foreach (fs::ls(fs::cfgpath('pkg')) as $file)
        {
            if (substr($file, -5) === '.json')
                $packages[] = substr($file, 0, -5);
        }

        return $packages;
    }

    /**
     * Check if provided package is actually namespace
     * and if it is, convert it to package name.
     * --
     * @param string $in
     * --
     * @return string
     */
    private static function ns_to_pkg($in)
    {
        if (strpos($in, '\\') !== false)
        {
            return pkg::by_namespace($in);
        }
        else
        {
            return $in;
        }
    }
}
