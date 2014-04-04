<?php

namespace Mysli;

class Dashboard
{
    private $output;
    private $tplp;
    private $web;
    private $session;
    private $users;
    private $translator;

    private $assets;
    private $debug = true;

    /**
     * Construct Dashboard
     * --
     * @param object $web     mysli/web
     * @param object $session mysli/session
     * @param object $users   mysli/users
     * @param object $output  ~output
     * @param object $tplp    ~tplp
     * @param object $i18n    ~i18n
     */
    public function __construct($web, $session, $users, $output, $tplp, $i18n)
    {
        $this->web = $web;
        $this->output = $output;
        $this->tplp = $tplp;
        $this->translator = $i18n->translator();
        $this->tplp->translator_set($this->translator);
        $this->session = $session;
        $this->users = $users;

        $this->assets = \Core\JSON::decode_file(ds(__DIR__, 'assets.json'), true);
        $this->register_functions();
    }

    /**
     * Register essential functions.
     * --
     * @return null
     */
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

    public function init_request($response, $method, $route)
    {
        // Before we can do anything, we must be sure user is logged in!
        if ($this->session->user()) {
            // We have user...
            $route_segments = explode('/', $route);

            // Preform logout action...
            if (\Core\Arr::element(1, $route_segments) === 'logout') {
                $this->session->destroy();
                $response->status_303_see_other($this->web->url('dashboard'));
                return;
            }

            // Do we have different path than /dashboard
            if (count($route_segments) > 1) {
                return $this->process_request($response, $method, $route);
            } else {
                // Dashboard - home
                return $this->dashboard($response);
            }
        } else {
            return $this->login($response, $method);
        }
    }

    private function dashboard($response)
    {
        $response->status_200_ok();

        $template = $this->tplp->template('dashboard');
        $this->output->add($template->render());
    }

    private function login($response, $method)
    {
        $response->status_200_ok();

        $template = $this->tplp->template('login');
        $template->variable_set('messages', []);
        $template->variable_set('username', '');
        $template->variable_set('remember_me', false);

        if ($method === 'post') {
            $user = $this->users->auth($_POST['username'], $_POST['password']);

            if ($user) {
                $this->session->create($user, (isset($_POST['remember_me']) ? null : 0));
                $response->status_303_see_other($this->web->url('dashboard'));
                return;
            } else {
                $template->variable_set('username', $_POST['username']);
                $template->variable_set('remember_me', isset($_POST['remember_me']));
                $template->variable_set('messages', [
                    $this->translator->translate('login_invalid_auth')
                ]);
            }
        }

        $this->output->add($template->render());
    }
}
