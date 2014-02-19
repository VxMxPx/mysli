<?php

namespace Mysli;

class Config
{
    private $config   = []; // All configurations values
    private $cache    = []; // Cached values, for faster access
    private $filename = ''; // The file to which we're writting.

    /**
     * Construct CONFIG
     * --
     * @param array  $info Info from the librarian
     */
    public function __construct(array $info)
    {
        // A little bit of magic...
        // Librarian always send, as the last element, _information_, which is an
        // array, containing the information about the request (e.g. - who required
        // particular library). In this case, we'll use this info, to construct
        // costumized config, containing only element meant for particular library.
        $library   = \Core\Arr::element('requested_by', $info);
        array_pop($library); // Remove self
        $library = array_pop($library);
        $library   = str_replace('/', '.', $library);
        $this->filename = datpath('config', $library . '.json');

        // If we have file, then load contents...
        if (file_exists($this->filename)) {
            $this->config = \Core\JSON::decode_file($this->filename, true);
        }
    }

    /**
     * Will write changes to file.
     * --
     * @return boolean
     */
    public function write()
    {
        return \Core\JSON::encode_file($this->filename, $this->config);
    }

    /**
     * Delete the config filename.
     * --
     * @return boolean
     */
    public function destroy()
    {
        // Empty cache and config...
        $this->cache = $this->config = [];

        if (file_exists($this->filename)) {
            return unlink($this->filename);
        } else {
            return true;
        }
    }

    /**
     * Dump current cache and config.
     * --
     * @return array
     */
    public function dump()
    {
        return [$this->cache, $this->config];
    }

    /**
     * Get Config Item by Path.
     * --
     * @param  string  $key      In format: key/subkey
     * @param  mixed   $default  Default value, if config isn't set
     * --
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $value = \Core\Arr::get_by_path($key, $this->config, $default);

        // We cache only when we assume it's not default value...
        if ($value !== $default) {
            $this->cache[$key] = $value;
        }

        // Return value in any case...
        return $value;
    }

    /**
     * Overwrite particular config key, this is temporary action,
     * the changes won't get saved, until you call $this->write();
     * --
     * @param   string  $path   In format: key/subkey
     * @param   mixed   $value
     * --
     * @return  void
     */
    public function set($path, $value)
    {
        # Clear cache to avoid conflicts
        $this->cache = [];

        \Core\Arr::set_by_path($path, $value, $this->config);
    }

    /**
     * Merge current config with new config.
     * --
     * @param  array $config
     * --
     * @return void
     */
    public function merge(array $config)
    {
        $this->cache = [];
        $this->config = \Core\Arr::merge($this->config, $config);
    }
}
