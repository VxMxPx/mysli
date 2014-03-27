<?php

namespace Mysli\Tplp;

class Template
{
    protected $translator;
    protected $filename;

    protected $variables = [];

    /**
     * Construct template object.
     * --
     * @param string $filename
     * @param object $translator -- Used in translations!
     */
    public function __construct($filename, $translator = null)
    {
        if (is_object($translator) && method_exists($translator, 'translate')) {
            $this->set_translator($translator);
        }

        $this->filename = $filename;
    }

    /**
     * Set translator!
     * --
     * @param object $translator
     * --
     * @return null
     */
    public function set_translator($translator)
    {
        $this->translator = $translator;
        $this->variables['tplp_translator_service'] = function () {
            call_user_func_array([$translator, 'translate'], func_get_args());
        };
    }

    /**
     * Set variables.
     * --
     * @param mixed $key - string|array
     * @param mixed $value
     * --
     * @throws \Core\ValueException If variable name is not valid: a-zA-Z0-9_ (1)
     * @throws \Core\ValueException If Variable start with: tplp_ (2)
     * --
     * @return null
     */
    public function data($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $var => $val) {
                $this->data($var, $val);
            }
            return;
        }

        if (!preg_match('/^[a-z_][a-z0-9_]*?$/i', $key)) {
            throw new \Core\ValueException(
                "Invalid variable name: `{$key}`.".
                'Variable can contain only: `a-zA-Z0-9_`.', 1
            );
        }

        if (substr($key, 0, 5) === 'tplp_') {
            throw new \Core\ValueException("Variable cannot start with: `tplp_`.", 2);
        }

        $this->variables[$key] = $value;
    }

    /**
     * Process template, execute code with variables, and return result (HTML).
     * --
     * @return string
     */
    public function render()
    {
        // Assign variables...
        foreach($this->variables as $var => $val) {
            $$var = $val;
        }

        ob_start();
            include($this->filename);
            $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Return template's PHP (processed template).
     * --
     * @return string
     */
    public function php()
    {
        return file_get_contents($this->filename);
    }
}
