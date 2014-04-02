<?php

namespace Mysli;

class Dashboard
{
    protected $output;
    protected $tplp;
    protected $web;

    protected $assets;
    protected $debug = true;

    public function __construct($web, $session, $output, $tplp, $i18n)
    {
        $this->web = $web;
        $this->output = $output;
        $this->tplp = $tplp;
        $this->tplp->translator_set($i18n->translator());

        $this->assets = \Core\JSON::decode_file(ds(__DIR__, 'assets.json'), true);
        $this->register_functions();
    }

    private function register_functions()
    {
        $this->tplp->function_register('dashurl', function ($value) {
            return $this->web->url('dashboard/' . $value);
        });
        $this->tplp->function_set('assets', function ($name) {

            // Invalid filename
            if (!isset($this->assets[$name])) return;

            // Include styles
            if (substr($name, -3) === 'css') {
                return '<link rel="stylesheet" type="text/css" href="' . $this->web->url('mysli/dashboard/dist/' . $name) . '">'  . "\n";
            }

            // Include script(s)
            $process = $this->debug
                ? $this->assets[$name]
                : [$name];

            $scripts = '';
            foreach ($process as $asset) {
                $url = $this->debug
                    ? $this->web->url('mysli/dashboard/src/' . $asset)
                    : $this->web->url('mysli/dashboard/dist/' . $asset);

                $scripts .= '<script src="' . $url . '"></script>' . "\n";
            }
            return $scripts;
        });
    }

    public function process_request($response, $method, $route)
    {
        $response->status_200_ok();
        $template = $this->tplp->template('login');
        $this->output->add($template->render());
    }
}
