<?php

namespace Mysli\Pkgm;

class Pkgm
{
    /**
     * Objects cache.
     * --
     * @var Mysli\Pkgm\Cache
     */
    private $cache;

    /**
     * Registry (list of enabled packages).
     * --
     * @var Mysli\Pkgm\Registry
     */
    private $registry;

    /**
     * Construct the pkgm main class.
     */
    public function __construct()
    {
        // Manually load exceptions, because autloader will use them!
        if (!class_exists('Mysli\\Pkgm\\DependencyException', false)) {
            include ds(__DIR__, 'exceptions/dependency.php');
        }
        if (!class_exists('Mysli\\Pkgm\\PackageException', false)) {
            include ds(__DIR__, 'exceptions/package.php');
        }

        // Load util!
        if (!class_exists('Mysli\\Pkgm\\Util', false)) {
            include ds(__DIR__, 'util.php');
        }

        // Load autoloader!
        if (!class_exists('Mysli\\Pkgm\\Autoloader', false)) {
            include ds(__DIR__, 'autoloader.php');
        }

        // Register autoloader
        spl_autoload_register(['\\Mysli\\Pkgm\\Autoloader', 'load']);

        // Construct registry!
        $this->registry = new Registry(datpath('pkgm/registry.json'));

        // Construct Registry, and add self to the cache!
        // (Only one instance of pkgm is allowed)
        $this->cache = new Cache();
        $this->cache->add('mysli/pkgm/pkgm', $this);
    }

    /**
     * Call method for particular class / package.
     * The input must be string in format:
     * vendor/package/sub_class::static_method
     * vendor/package->method
     * --
     * @param  string $what
     * @param  array  $arguments (if any)
     * --
     * @return mixed
     */
    public function call($what, array $arguments = [])
    {
        $what_r  = explode((strpos($what, '->') !== false ? '->' : '::'), $what);
        $package = $what_r[0];
        $method  = $what_r[1];
        $class   = Util::to_class($package);

        if (!class_exists($class, false)) {
            if (!$this->autoload($class)) {
                throw new PackageException(
                    "Cannot autoload: `" . $class . "` for: `{$what}`.", 1
                );
            }
        }

        // Non-static call?
        if (strpos($what, '->') !== false) {
            $callable = $this
                ->factory(Util::to_pkg($package, Util::BASE))
                ->produce(Util::to_pkg($package, Util::FILE));
        } else {
            $callable = $class;
        }

        return call_user_func_array(
            [
                $callable,
                $method
            ],
            $arguments
        );
    }

    /**
     * Get registry object.
     * --
     * @return Mysli\Pkgm\Registry
     */
    public function registry()
    {
        return $this->registry;
    }

    /**
     * Get factory for particular package.
     * --
     * @param  string $package
     * --
     * @return Mysli\Pkgm\Factory
     */
    public function factory($package)
    {
        return new Factory($package, $this->registry, $this->cache);
    }

    /**
     * Will enable / disable particular package.
     * --
     * @param  string $package
     * --
     * @return Mysli\Pkgm\Control
     */
    public function control($package)
    {
        return new Control($package, $this->registry);
    }
}
