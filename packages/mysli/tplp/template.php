<?php

namespace Mysli\Tplp;

class Template
{
    protected $translator;
    protected $filename;

    protected $variables = [];

    public function __construct($filename, $translator = null)
    {
        $this->translator = $translator;
        $this->filename = $filename;
    }

    /**
     * Set variables.
     * --
     * @param mixed $key - string|array
     * @param mixed $value
     * --
     * @return null
     */
    public function data($key, $value = null)
    {
        if (is_array($key)) {
            $this->variables = array_merge($this->variables, $variables);
        } else {
            $this->variables[$key] = $value;
        }
    }

    /**
     * Process template, execute code with variables, and return result (HTML).
     * --
     * @return string
     */
    public function render()
    {

    }

    /**
     * Return template's PHP (processed template).
     * --
     * @return string
     */
    public function php()
    {

    }

    /**
     * Return raw original unprocessed template.
     * --
     * @return string
     */
    public function raw()
    {
    }
}
