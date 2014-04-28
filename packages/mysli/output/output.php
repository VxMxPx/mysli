<?php

namespace Mysli\Output;

class Output
{
    // Whole output
    protected $output_cache = [];

    /**
     * Add output string.
     * --
     * @param string  $contents
     * @param mixed   $key      Either false for automatic, or particular key.
     */
    public function add($contents, $key = false)
    {
        if (!$key) {
            $this->output_cache[] = $contents;
        }
        else {
            if (isset($this->output_cache[$key])) {
                $contents = $this->output_cache[$key] . $contents;
            }

            $this->output_cache[$key] = $contents;
        }
    }

    /**
     * Replace output if exists, otherwise just add it.
     * --
     * @param  string $key
     * @param  string $contents
     * --
     * @return void
     */
    public function replace($contents, $key)
    {
        $this->output_cache[$key] = $contents;
    }

    /**
     * Will take particular output (it will return it, and then erase it)
     * --
     * @param   string  $key Get particular output item.
     *                       If set to false, will get all.
     * --
     * @return  mixed
     */
    public function take($key = false)
    {
        $output = $this->as_string($key);
        $this->clear($key);

        return $output;
    }

    /**
     * Return one part or whole output as a string (will escape HTML tags).
     * --
     * @param  mixed $key
     * --
     * @return string
     */
    public function as_string($key = false)
    {
        if (!$key) { $html = implode("\n", $this->output_cache); }
        $html = \Core\Arr::element($key, $this->output_cache, null);
        return htmlentities($html);
    }

    /**
     * Return one part or whole output as a HTML.
     * --
     * @param  mixed $key
     * --
     * @return string
     */
    public function as_html($key = false)
    {
        if (!$key) { return implode("\n", $this->output_cache); }
        return \Core\Arr::element($key, $this->output_cache, null);
    }

    /**
     * Do we have particular key? Or any output at all?
     * --
     * @param   string  $key
     * --
     * @return  boolean
     */
    public function has($key = false)
    {
        if (!$key) {
            return is_array($this->output_cache) && !empty($this->output_cache);
        }
        else {
            return isset($this->output_cache[$key]);
        }
    }

    /**
     * Clear Output. If key is provided only particular item will be cleared.
     * Otherwise all cache will be cleared.
     * --
     * @param   string  $key
     * --
     * @return  void
     */
    public function clear($key = false)
    {
        if (!$key) {
            $this->output_cache = [];
        }
        else {
            if (isset($this->output_cache[$key])) {
                unset($this->output_cache[$key]);
            }
        }
    }

    /**
     * Return all output items as an array.
     * --
     * @return array
     */
    public function as_array()
    {
        return $this->output_cache;
    }
}
