<?php

namespace Mysli\Dashboard;

class Registry
{
    private $package;

    /**
     * Construct Registry
     * @param MysliPkgmTrace $trace
     */
    public function __construct(\Mysli\Pkgm\Trace $trace)
    {
        $this->package = $trace->get_last();
    }

    /**
     * Save registry changes.
     * --
     * @return boolean
     */
    protected function write(array $registry)
    {
        return \Core\JSON::encode_file(datpath('mysli.dashboard/registry.json'), $registry);
    }

    /**
     * Get registry file.
     * --
     * @return array
     */
    protected function get_registry()
    {
        return \Core\JSON::decode_file(datpath('mysli.dashboard/registry.json'));
    }

    /**
     * Add new entry to the registry file.
     * --
     * @param string $url   Entrie's url (vendor/package)
     * @param string $class Sub class (something = vendor/package/something)
     * --
     * @return boolean
     */
    public function add($url = null, $class = 'dash')
    {
        if (!$url) $url = $this->package;
        $registry = $this->get_registry();
        $registry[$url][$this->package] = $this->package . '/' . $class;
        return $this->write($registry);
    }

    /**
     * Remove entry from the registry file.
     * --
     * @param  string $url
     * --
     * @return boolean
     */
    public function remove($url = null)
    {
        $registry = $this->get_registry();

        if ($url && (!isset($registry[$url]) || empty($registry[$url]))) return true;

        if ($url) {
            if (isset($registry[$url][$this->package]))
                unset($registry[$url][$this->package]);
        } else {
            foreach ($registry as $rurl => $packages)
                if (isset($packages[$this->package]))
                    unset($registry[$rurl][$this->package]);
        }

        return $this->write($registry);
    }
}
