<?php

namespace Mysli\Dashboard;

class Service
{
    private $output;
    private $tplp;
    private $web;
    private $session;
    private $users;
    private $translator;

    /**
     * Construct Dashboard
     * --
     * @param object $web        mysli/web
     * @param object $session    mysli/session
     * @param object $users      mysli/users
     * @param object $output     mysli/output
     * @param object $tplp       mysli/tplp
     * @param object $i18n       mysli/i18n
     */
    public function __construct($web, $session, $users, $output, $tplp, $i18n) {
        $this->web     = $web;
        $this->output  = $output;
        $this->tplp    = $tplp;
        $this->session = $session;
        $this->users   = $users;

        // Set translator
        $this->translator = $i18n->translator();
        $this->tplp->set_translator($this->translator);

        // Register template functions
        $this->register_functions();
    }

    /**
     * Register essential functions.
     * --
     * @return null
     */
    private function register_functions()
    {
        // dashurl
        $this->tplp->register_function('dashurl', function ($value) {
            return $this->web->url('dashboard/' . $value);
        });
    }

    public function init($response, $method, $route)
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
                // Dashboard index
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
        $template->set_variable('user', $this->session->user());
        $this->output->add($template->render());
    }

    private function login($response, $method)
    {
        $response->status_200_ok();

        $template = $this->tplp->template('login');
        $template->set_variable('messages', []);
        $template->set_variable('username', '');
        $template->set_variable('remember_me', false);

        if ($method === 'post') {
            $user = $this->users->auth($_POST['username'], $_POST['password']);

            if ($user) {
                $this->session->create($user, (isset($_POST['remember_me']) ? null : 0));
                $response->status_303_see_other($this->web->url('dashboard'));
                return;
            } else {
                $template->set_variable('username', $_POST['username']);
                $template->set_variable('remember_me', isset($_POST['remember_me']));
                $template->set_variable('messages', [
                    $this->translator->translate('login_invalid_auth')
                ]);
            }
        }

        $this->output->add($template->render());
    }
}
