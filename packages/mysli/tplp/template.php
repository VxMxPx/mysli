<?php

namespace Mysli\Tplp;

class Template
{
    use \Mysli\Tplp\ExtData;

    private $translator;
    private $filename;

    private $functions = [];
    private $variables = [];

    /**
     * Construct template object.
     * --
     * @param string $filename
     * @param object $translator -- Used in translations!
     */
    public function __construct(
        $filename,
        $translator = null,
        array $variables = [],
        array $functions = []
    ) {
        $this->filename = $filename;

        $this->functions = $functions;
        $this->variables = $variables;

        if (is_object($translator) && method_exists($translator, 'translate')) {
            $this->set_translator($translator);
        }
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
            return call_user_func_array([$this->translator, 'translate'], func_get_args());
        };
    }

    /**
     * Process template, execute code with variables, and return result (HTML).
     * --
     * @return string
     */
    public function render()
    {
        // Assign variables...
        foreach(array_merge( $this->variables, $this->functions ) as $tplp_template_var => $tplp_template_val) {
            $$tplp_template_var = $tplp_template_val;
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
