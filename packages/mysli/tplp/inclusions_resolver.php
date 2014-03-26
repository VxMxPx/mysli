<?php

namespace Mysli\Tplp;

class InclusionsResolver
{
    /**
     * List of templates which are being processed at the moment.
     * @var array
     */
    protected $processing = [];

    /**
     * List of all templates (to be resolved)
     * @var array
     */
    protected $templates = [];

    /**
     * List of templates which are already resolved.
     * @var array
     */
    protected $resolved  = [];

    /**
     * @param array $templates List of templates handle => string
     */
    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * Will resolve one template.
     * --
     * @param string $handle
     * @param string $template
     * --
     * @throws \Mysli\Tplp\ParserException If trying to resolve template which is
     *         already in the process of being resolved.
     * --
     * @return array
     */
    protected function resolve_template($handle, $template)
    {
        if (in_array($handle, $this->processing)) {
            throw new \Mysli\Tplp\ParserException(
                'Trying to resolve template, which is already processing.'.
                "Seems like infinite loop.\nTemplate: '{$handle}'" .
                "\nProcessing: '" . implode(', ', $this->processing) . "'",
                1
            );
        }

        // And itself to processing array...
        $this->processing[] = $handle;

        // Find regular inclusions
        $template =
        preg_replace_callback(
            '/::use ([a-zA-Z0-9_\-]+)$/m',
            function($match) {

                // File to include (handle)
                $file = trim($match[1]);

                if (!isset($this->templates[$file])) {
                    throw new \Core\NotFoundException("Template file doesn't exists: `{$file}`.", 1);
                }

                if (!isset($this->resolved[$file])) {
                    $this->resolved[$file] = $this->resolve_template($file, $this->templates[$file]);
                }

                return $this->resolved[$file];
            },
            $template
        );

        // Find master
        // If template contains: ::use layout as master
        // Then this will be set to mater's content
        $master = false;
        $template =
        preg_replace_callback(
            '/([[:cntrl:]]+)?::use ([a-zA-Z0-9_\-]+) as master/',
            function($match) use (&$master) {

                // File to include (handle)
                $file = trim($match[2]);

                if (!isset($this->templates[$file])) {
                    throw new \Core\NotFoundException("Template file doesn't exists: `{$file}`.", 1);
                }

                if (!isset($this->resolved[$file])) {
                    $this->resolved[$file] = $this->resolve_template($file, $this->templates[$file]);
                }

                $master = $this->resolved[$file];
                return '';
            },
            $template
        );
        if ($master) {
            $template = str_replace('::yield', trim($template), $master);
        }

        $this->processing = \Core\Arr::delete_by_value_all($handle, $this->processing);

        return $template;
    }

    /**
     * Resolve templates.
     * --
     * @return array (list of resolved templates)
     */
    public function resolve()
    {
        foreach ($this->templates as $handle => $template) {
            $this->resolved[$handle] = $this->resolve_template($handle, $template);
        }

        return $this->resolved;
    }

    /**
     * Get list of resolved templates.
     * --
     * @return array
     */
    public function get_resolved()
    {
        return $this->resolved;
    }
}
