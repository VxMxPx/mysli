<?php

namespace Mysli\Pkgm;

class Factory
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
     * List of cached packages.
     * --
     * @var \Mysli\Pkgm\Cache
     */
    private $cache;

    /**
     * While constructing packages (+dependencies), used in #pkgm_trace.
     * --
     * @var array
     */
    private static $producing = [];


    /**
     * Construct factory for package.
     * --
     * @param string               $package
     * @param \Mysli\Pkgm\Registry $registry
     * @param \Mysli\Pkgm\Cache    $cache
     */
    public function __construct($package, $registry, $cache)
    {
        // Resolve role
        if (substr($package, 0, 1) === '@') {
            $package = $registry->get_role($package);
        }
        $this->package  = $package;
        $this->registry = $registry;
        $this->cache    = $cache;
    }

    /**
     * Check if package's class can be produced (file exists, meta entry exists,
     * meta entry will not return null)
     * --
     * @param  string $file
     * --
     * @return boolean
     */
    public function can_produce($file = null)
    {
        if (!$file) {
            $file    = Util::to_path($this->package, Util::FILE);
            $package = $this->package;
        } else {
            $package = Util::to_path($this->package . '/' . $file);
        }

        if (!file_exists(pkgpath($package))) {
            return false;
        }

        // Get package info
        $info = $this->registry->get_details($this->package);

        // Key exists for this class?
        if (!isset($info['factory']) || !isset($info['factory'][$file])) {
            return false;
        }

        $instantiation = Control::process_factory_entry($info['factory'][$file])['instantiation'];

        if ($instantiation === 'null') {
            return false;
        }

        return true;
    }

    /**
     * Construct package's class - this will auto-manage all dependencies.
     *
     * This is not a regular factory, it will respect the meta.json's
     * _factory_ instructions:
     * --
     * singleton: Only one instance of class will be constructed (and then cached)
     * construct: Always construct fresh copy of object.
     * null     : Return null, (no acces to the class)
     * name     : Return full class name, so that it can be manuallt constructed.
     * --
     * @param string $package
     * --
     * @throws \Core\ValueException If package is not enabled. (1)
     * @throws \Core\ValueException If factory/file entry missing in meta.json. (2)
     * --
     * @return mixed
     */
    public function produce($file = null)
    {
        if (!$file) {
            $file    = Util::to_pkg($this->package, Util::FILE);
            $package = $this->package;
        } else {
            $package = $this->package . '/' . $file;
        }

        $class = Util::to_class($package);

        // Check if I'm enabled...
        // if (!$this->registry->is_enabled($this->package)) {
        //     throw new \Core\ValueException(
        //         "Cannot call `produce` if package is not enabled, for: '{$package}'.", 1
        //     );
        // }

        // Check if we have it cached...
        if ($this->cache->has($package)) {
            return $this->cache->get($package);
        }

        // Get package info
        $info = $this->registry->get_details($this->package);

        // Key exists for this class?
        if (!isset($info['factory']) || !isset($info['factory'][$file])) {
            throw new \Core\ValueException(
                "Missing entry: `factory { '{$file}' : 'instantiation_type()' }` in `meta.json` for: `{$package}`.",  2
            );
        }

        $entry = Control::process_factory_entry($info['factory'][$file]);
        $instantiation = $entry['instantiation'];
        $inject = $entry['inject'];

        switch ($instantiation) {
            case 'null':
                return false;

            case 'name':
                return $class;
        }

        // Instantiate class now...
        self::$producing[] = [$this->package, $package];

        $rfc = new \ReflectionClass($class);

        if ($rfc->hasMethod('__construct')) {
            if (!empty($inject)) {
                try {
                    $dependencies = $this->get_dependencies($inject);
                } catch (\Exception $e) {
                    self::$producing = [];
                    throw $e;
                }
            } else {
                $dependencies = [];
            }
            $instance = $rfc->newInstanceArgs($dependencies);
        } else {
            $instance = $rfc->newInstanceWithoutConstructor();
        }

        // Do we have instruction to be instantiated once?
        if ($instantiation === 'construct') {
            $this->cache->add($package, $instance);
        }

        array_pop(self::$producing);

        return $instance;
    }

    /**
     * Construct dependencies from the list. Will return an array of objects.
     * --
     * @param  array $dependencies
     * --
     * @throws DependencyException If dependency is not enabled. (1)
     * @throws DependencyException If dependency version is not sufficient. (2)
     * --
     * @return array
     */
    public function get_dependencies(array $dependencies)
    {
        $result = [];

        foreach ($dependencies as $dependency) {

            // Check if we have #<speacial instruction>
            if (substr($dependency, 0 ,1) === '#') {
                if ($dependency === '#pkgm_trace') {
                    $result[$dependency] = array_slice(self::$producing, 0, -1);
                } else {
                    $result[$dependency] = null;
                }
                continue;
            }

            // Check if we have self: reference...
            if (substr($dependency, 0, 5) === 'self:') {
                $result[$dependency] = $this->produce(substr($dependency, 5));
                continue;
            }

            // Resolve role if exists...
            $dependency = $this->registry->get_role($dependency);

            // Resolve the dependency
            if ($this->registry->is_enabled($dependency)) {
                $details = $this->registry->get_details($dependency);
            } else {
                throw new DependencyException(
                    "Dependency is not enabled: '{$dependency}' cannot proceed.", 1
                );
            }

            $factory = new Factory(Util::to_pkg($dependency, Util::BASE), $this->registry, $this->cache);
            $instance = $factory->produce(Util::to_pkg($dependency, Util::FILE));

            unset($factory);

            $result[$dependency] = $instance;
        }

        return $result;
    }

}
