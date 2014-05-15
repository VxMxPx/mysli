<?php

namespace Mysli\Dashboard;

class Setup
{
    private $web;
    private $event;
    private $tplp;
    private $i18n;

    private $ecsi;

    public function __construct($csi, $web, $users, $event, $tplp, $i18n)
    {
        $this->web   = $web;
        $this->event = $event;
        $this->tplp  = $tplp;
        $this->i18n  = $i18n;

        $this->users = $users;

        $this->ecsi = new $csi('mysli/dashboard/enable');
        $this->ecsi->paragraph('Create your first user!');
        $this->ecsi->input(
            'username',
            'Username',
            'root@localhost',
            function (&$field) {
                if (strpos($field['value'], '@') === false) {
                    $field['messages'] = 'Please enter a valid email address.';
                    return false;
                }
                return true;
            }
        );
        $this->ecsi->password(
            'password',
            'Password',
            function (&$field) {
                if (strlen($field['value']) < 3) {
                    $field['messages'] = 'Password should be at least 3 characters long...';
                    return false;
                }
                return true;
            }
        );
    }

    public function before_enable()
    {
        // CSI needs to be successful before we can continue
        if ($this->ecsi->status() !== 'success') return $this->ecsi;

        \Core\FS::dir_copy(
            pkgpath('mysli/dashboard/assets'),
            $this->web->path('mysli/dashboard')
        );

        $this->tplp->create_cache();
        $this->i18n->create_cache();

        $this->event->register('mysli/web/route:*<dashboard*>', 'mysli/dashboard/service->init');

        // Add default user...
        $user = $this->users->create([
            'email'    => $this->ecsi->get('username'),
            'password' => $this->ecsi->get('password'),
            'is_super' => true
        ]);
        if ($user) {
            $user->save();
        }

        return true;
    }

    public function after_disable()
    {
        \Core\FS::dir_remove($this->web->path('mysli/dashboard'));
        $this->tplp->remove_cache();
        $this->i18n->remove_cache();
        $this->event->unregister('mysli/web/route:*<dashboard*>', 'mysli/dashboard/service->init');
        return true;
    }
}
