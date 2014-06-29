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
        // if (substr($package, 0, 1) === '@') {
        //     $package = $registry->get_role($package);
        // }
        $this->package  = $package;
        $this->registry = $registry;
        $this->cache    = $cache;
    }

    private function get_instantiation_instructions(\ReflectionClass $rfc)
    {
        $traits = $rfc->getTraits();

        if (!is_array($traits)) {
            return;
        }
        if (isset($traits['Mysli\\Core\\Pkg\\Singleton'])) {
            return 'singleton';
        }
        else if (isset($traits['Mysli\\Core\\Pkg\\Prevent'])) {
            return 'prevent';
        }
        return 'construct';
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
            $file = Util::to_pkg($this->package, Util::FILE);
            $pkgf = $this->package;
        } else {
            $pkgf = $this->package . '/' . $file;
        }

        $class = Util::to_class($pkgf);

        if (!file_exists(pkgpath($this->package, $file . '.php'))) {
            return false;
        }

        // Get package info
        $info = $this->registry->get_details($this->package);

        // Key exists for this class?
        // if (!isset($info['factory']) || !isset($info['factory'][$file])) {
        //     return false;
        // }

        // $instantiation = Control::process_factory_entry($info['factory'][$file])['instantiation'];

        $instantiation = $this->get_instantiation_instructions(new \ReflectionClass($class));

        if ($instantiation === 'prevent') {
            return false;
        }

        return true;
    }

    /**
     * Construct package's class - this will auto-manage all dependencies.
     *
     * --
     * singleton: Only one instance of class will be constructed (and then cached)
     * construct: Always construct fresh copy of object.
     * null     : Return null, (no acces to the class)
     * name     : Return full class name, so that it can be manuallt constructed.
     * --
     * @param string $package
     * --
     * @throws \Core\ValueException If package is not enabled. (1)
     * @throws \Mysli\Pkgm\PackageException If instantiation is `prevent`. (1)
     * @throws \Mysli\Pkgm\PackageException If constructor argument has not type. (2)
     * --
     * @return mixed
     */
    public function produce($file = null)
    {
        if (!$file) {
            $file = Util::to_pkg($this->package, Util::FILE);
            $pkgf = $this->package;
        } else {
            $pkgf = $this->package . '/' . $file;
        }

        $class = Util::to_class($pkgf);

        // Previous package (the one which required this...)
        if (isset(self::$producing[count(self::$producing) - 1])) {
            $required_by = self::$producing[count(self::$producing) - 1][0];
        } else {
            $required_by = false;
        }

        // Check if we have it cached...
        if ($this->cache->has($pkgf)) {
            return $this->cache->get($pkgf);
        }

        // Check if we have it cached in relation to the package that required it.
        if ($required_by && $this->cache->has("{$pkgf}::{$required_by}")) {
            return $this->cache->get("{$pkgf}::{$required_by}");
        }

        // Get package info
        // $info = $this->registry->get_details($this->package);

        // Key exists for this class?
        // if (!isset($info['factory']) || !isset($info['factory'][$file])) {
        //     throw new \Core\ValueException(
        //         "Missing entry: `factory { '{$file}' : 'instantiation_type()' }` in `mysli.pkg.json` for: `{$pkgf}`.",  2
        //     );
        // }

        // $entry = Control::process_factory_entry($info['factory'][$file]);
        // $instantiation = $entry['instantiation'];
        // $inject = $entry['inject'];

        // switch ($instantiation) {
        //     case 'null':
        //         return false;

        //     case 'name':
        //         return $class;
        // }

        // Instantiate class now...
        self::$producing[] = [$this->package, $pkgf];

        $rfc = new \ReflectionClass($class);

        $instantiation = $this->get_instantiation_instructions($rfc);

        if ($instantiation === 'prevent') {
            throw new \Mysli\Pkgm\PackageException("Auto construction is prevented for: `{$pkgf}`.", 1);
        }

        // do we require trace...
        // if ($rfc->hasProperty('pkg_trace')) {
        //     $pkg_trace = $rfc->getProperty('pkg_trace');
        //     $pkg_trace->setValue(array_slice(self::$producing, 0, -1));
        //     $pkg_trace->setAccessible(false);
        // }

        if ($rfc->hasMethod('__construct')) {

            $inject = $rfc->getMethod('__construct')->getParameters();
            $inject = array_map(function ($arg) use ($class) {
                $arg_class = $arg->getClass();
                if (!$arg_class) {
                    throw new \Mysli\Pkgm\PackageException("Constructor argument have no type: `{$arg->name}` for: `{$class}`.", 2);
                }
                return Util::to_pkg($arg_class->name);
            }, $inject);

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
        if ($instantiation === 'singleton') {
            $this->cache->add($pkgf, $instance);
        } else if ($required_by && $instantiation === 'construct') {
            // Even if not singleton, we'll still cache it for when is required
            // by the same package twice
            $this->cache->add("{$pkgf}::{$required_by}", $instance);
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
    private function get_dependencies(array $dependencies)
    {
        $result = [];

        foreach ($dependencies as $dependency) {

            // Check if we have #<speacial instruction>
            // if (substr($dependency, 0 ,1) === '#') {
            //     if ($dependency === '#pkgm_trace') {
            //         $result[$dependency] = array_slice(self::$producing, 0, -1);
            //     } else {
            //         $result[$dependency] = null;
            //     }
            //     continue;
            // }

            // Check if we have self: reference...
            // if (substr($dependency, 0, 5) === 'self:') {
            //     $result[$dependency] = $this->produce(substr($dependency, 5));
            //     continue;
            // }

            // Resolve role if exists...
            // $dependency = $this->registry->get_role($dependency);

            // Resolve the dependency
            if (!$this->registry->is_enabled(Util::to_pkg($dependency, Util::BASE))) {
                throw new DependencyException(
                    "Dependency is not enabled: '{$dependency}' cannot proceed.", 1
                );
            }

            if ($dependency === 'mysli/pkgm/trace') {
                $result[$dependency] = new \Mysli\Pkgm\Trace(self::$producing);
                continue;
            }

            $factory = new Factory(Util::to_pkg($dependency, Util::BASE), $this->registry, $this->cache);
            $instance = $factory->produce(Util::to_pkg($dependency, Util::FILE));

            unset($factory);

            $result[$dependency] = $instance;
        }

        return $result;
    }

}
