<?php

namespace Mysli\Dashboard;

class Dash
{
    private $dash;
    private $i18n;
    private $tplp;
    private $token;
    private $users;

    public function __construct(
        Dashboard $dash,
        \Mysli\Output\Output $output,
        \Mysli\I18n\I18n $i18n,
        \Mysli\Tplp\Tplp $tplp,
        \Mysli\Token\Token $token,
        \Mysli\Users\Users $users
    ) {
        $this->output = $output;
        $this->dash = $dash;
        $this->i18n = $i18n;
        $this->tplp = $tplp;
        $this->token = $token;
        $this->users = $users;

        // $this->dash->set_translator($i18n);
        // $this->dash->set_template($tplp);

        // // Set translator
        $this->translator = $i18n->translator();
        $this->tplp->set_translator($this->translator);
    }

    public function init()
    {
        $dash->template('dashboard');
        $dash->js($this->assets->js());
        $dash->css($this->assets->css());
        return $this->dash->as_json();
    }

    public function get_index()
    {
        $this->output->add($this->tplp->template('dashboard')->render());
    }

    public function get_script()
    {
        $require = isset($_GET['require']) ? $_GET['require'] : false;
        if (!$require || !preg_match('/[a-z0-9\/\-_]+/', $require)) {
            return;
        }
    }

    public function get_login()
    {
        $dash->template('login');
        return $this->dash->as_json();
    }

    public function post_login(array $post)
    {
        $user = $this->users->auth($post['username'], $post['password']);
        $this->dash->status(!!$user);
        if ($user) {
            $token = $this->token->create($user->id());
            $this->dash->data('toke', $token);
        } else {
            $this->dash->messages->warn('@login_invalid_auth');
        }
        return $this->dash->as_json();
    }
}
