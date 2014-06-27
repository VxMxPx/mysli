<?php
namespace Mysli\Ui;

class Ui
{
    private $output;
    private $tplp;
    private $web;

    public function __construct($tplp, $output, $web)
    {
        $this->tplp = $tplp;
        $this->output = $output;
        $this->web = $web;
    }

    public function examples($response, $method, $route)
    {
        $response->status_200_ok();

        $route = explode('/', $route);
        $route = isset($route[1]) ? $route[1] : 'alerts';

        $this->tplp->set_variable('get_alt', $this->get_alt());
        $this->tplp->set_variable('get_alt_invert', $this->get_alt(true));
        $this->tplp->set_variable('alt_link', $this->alt_link($route));
        $this->tplp->set_function('get_navigation', function () use ($route) {
            $files = scandir(ds(__DIR__, 'templates'));
            $links = [];
            foreach ($files as $file) {
                if (substr($file, -10) !== '.tplm.html' || $file === 'index.tplm.html') { continue; }
                $clean = substr($file, 0, -10);
                if ($clean === $route) {
                    $links[] = '<strong>' . ucfirst($clean) . '</strong>';
                } else {
                    $links[] = '<a href="' . $this->web->url('mysli-ui-examples/' . $clean) . '">' . ucfirst($clean) . '</a>';
                }
            }
            return implode(' | ', $links);
        });

        $template = $this->tplp->template($route);
        $template->set_variable('title', ucfirst($route));

        $this->output->add($template->render());
    }

    private function get_alt($double = false) {
        if ((isset($_GET['alt']) and $_GET['alt'] === 'true')) {
            $alt = true;
        } else {
            $alt = false;
        }
        $alt = $double ? !$alt : $alt;
        return ($alt ? 'alt' : '');
    }

    private function alt_link($uri) {
        return '<a href="' . $this->web->url_query(
            'alt',
            (isset($_GET['alt']) && $_GET['alt'] === 'true' ? 'false' : 'true'),
            "mysli-ui-examples/{$uri}/"
        ) .
        '">Inverse</a>';
    }
}
