<?php

namespace Mysli\Dashboard;

class Service
{
    use \Mysli\Core\Pkg\Singleton;

    private $output;
    private $web;
    private $tplp;
    private $token;
    private $pkgm;

    private $registry;
    private $allowed_methods = [
        'GET', 'POST', 'DELETE', 'PUT'
    ];
    private $no_login = [
        'mysli/dashboard' => [
            'script',
            'login',
            'index'
        ]
    ];

    /**
     * Construct Dashboard
     */
    public function __construct(
        \Mysli\Web\Web $web,
        \Mysli\Output\Output $output,
        \Mysli\Tplp\Tplp $tplp,
        \Mysli\Token\Token $token,
        \Mysli\Pkgm\Pkgm $pkgm
    ) {
        $this->web    = $web;
        $this->output = $output;
        $this->tplp   = $tplp;
        $this->token  = $token;
        $this->pkgm   = $pkgm;

        // Load registry file
        $this->registry = \Core\JSON::decode_file(datpath('mysli.dashboard/registry.json'));

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

    /**
     * Get controller on which action will be called.
     * --
     * @param  string $controller
     * --
     * @return object
     */
    private function get_controller($controller)
    {
        $segments = explode('/', $controller);
        $package = implode('/', array_slice($segments, 0, 2));
        $class = array_pop($segments);

        $factory = $this->pkgm->factory($package);

        if (!is_object($factory) || !$factory->can_produce($class)) { return false; }

        return $factory->produce($class);
    }

    /**
     * Initialize service - handle requests.
     * --
     * @param  \Mysli\Response\Response $response
     * @param  string                   $method
     * @param  string                   $route
     * --
     * @return null
     */
    public function init($response, $method, $route)
    {
        // Resolve route, action, segments and method
        $route_segments = explode('/', $route);
        $route = implode('/', array_slice($route_segments, 1, 2));
        $action = array_slice($route_segments, 3, 1);
        $action = trim(array_pop($action));
        if (!$action) {
            $action = 'index';
        }
        $segments = array_splice($route_segments, 4);
        if (in_array(strtoupper($method), $this->allowed_methods)) {
            $method = strtolower($method);
        } else {
            $response->status_500_internal_server_error();
            return;
        }

        if (!$route) {
            $route = 'mysli/dashboard';
            $method = 'get';
            $action = 'index';
        }

        // Resolve access...
        $access = false;
        if (isset($this->no_login[$route])) {
            if (in_array($action, $this->no_login[$route])) {
                $access = true;
            }
        } else {
            // Deal with the token...
        }

        if (!$access) {
            $response->status_401_unauthorized();
            return;
        }

        // Get controller, find and execute method
        $controller = $this->get_controller($route . '/dash');
        if (!$controller) {
            $response->status_404_not_found();
            return;
        }

        if (method_exists($controller, $method.'_'.$action)) {
            $call = $method.'_'.$action;
        } elseif (method_exists($controller, 'any_'.$action)) {
            $call = 'any_'.$action;
        } elseif (method_exists($controller, 'not_found')) {
            $call = 'not_found';
            array_unshift($segments, $action);
        } else {
            $response->status_404_not_found();
            return;
        }

        $response->status_200_ok();
        return call_user_func_array([$controller, $call], $segments);







        // // get token
        // $token = isset($_GET['token']) ? $_GET['token'] : null;

        // // validate token
        // if ($token) {
        //     $uid = $this->token->get($token);
        // } else $uid = false;

        // // first access to the dashboard
        // if (!$action) {
            // $controller = $this->get_controller('mysli/dashboard/dash');
        //     if (!$controller) {
        //         $response->status_500_internal_server_error();
        //         return;
        //     }
        // }

        // $response->status_200_ok();
        // $this->output->add($controller->get_index());

        // $response->status_200_ok();
        // $route_segments = explode('/', $route);

        // if (\Core\Arr::element(1, $route_segments) === 'login' && $method === 'post') {
        //     $response->content_type_json();
        //     $this->output->add( json_encode($this->login($_POST)) );
        //     return;
        // }

        // $template = $this->tplp->template('dashboard');
        // $this->output->add($template->render());
        // // Before we can do anything, we must be sure user is logged in!
        // if ($this->session->user()) {
        //     // We have user...
        //     $route_segments = explode('/', $route);

        //     // Preform logout action...
        //     if (\Core\Arr::element(1, $route_segments) === 'logout') {
        //         $this->session->destroy();
        //         $response->status_303_see_other($this->web->url('dashboard'));
        //         return;
        //     }

        //     // Do we have different path than /dashboard
        //     if (count($route_segments) > 1) {
        //         return $this->process_request($response, $method, $route);
        //     } else {
        //         // Dashboard index
        //         return $this->dashboard($response);
        //     }
        // } else {
        //     return $this->login($response, $method);
        // }
    }

    // private function login($data)
    // {
    //     $user = $this->users->auth($data['username'], $data['password']);
    //     if ($user) {
    //         $token = $this->token->create($user->id());
    //         return [
    //             'status' => 'success',
    //             'token'  => $token
    //         ];
    //     } else {
    //         return [
    //             'status'  => 'failed',
    //             'message' => $this->translator->translate('login_invalid_auth')
    //         ];
    //     }
    // }

    // private function dashboard($response)
    // {
    //     $response->status_200_ok();

    //     $template = $this->tplp->template('dashboard');
    //     $template->set_variable('user', $this->session->user());
    //     $this->output->add($template->render());
    // }

    // private function login($response, $method)
    // {
    //     $response->status_200_ok();

    //     $template = $this->tplp->template('login');
    //     $template->set_variable('messages', []);
    //     $template->set_variable('username', '');
    //     $template->set_variable('remember_me', false);

    //     if ($method === 'post') {
    //         $user = $this->users->auth($_POST['username'], $_POST['password']);

    //         if ($user) {
    //             $this->session->create($user, (isset($_POST['remember_me']) ? null : 0));
    //             $response->status_303_see_other($this->web->url('dashboard'));
    //             return;
    //         } else {
    //             $template->set_variable('username', $_POST['username']);
    //             $template->set_variable('remember_me', isset($_POST['remember_me']));
    //             $template->set_variable('messages', [
    //                 $this->translator->translate('login_invalid_auth')
    //             ]);
    //         }
    //     }

    //     $this->output->add($template->render());
    // }
}
