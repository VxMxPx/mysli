<?php

namespace mysli\cms\dash\setup;

__use(__namespace__, '
    mysli/framework/csi
    mysli/framework/event
    mysli/util/i18n
    mysli/web/users
');

function enable($csi=null) {

    if (!$csi) {
        $csi = new csi('mysli/cms/dash/enable');
        $csi->text('You need to create one user account...');
        $csi->input(
        'email',
        'Email: ',
        'root@localhost',
        function (&$field) {
            if (!strpos($field['value'], '@')) {
                $field['messages'] = 'Please enter a valid email address.';
                return false;
            }
            if (users::exists(users::get_id_from_uname($field['value']))) {
                $field['messages'] = 'User with such email already exists.';
                return false;
            }
            return true;
        });
        $csi->password(
        'password',
        'Password: ',
        function (&$field) {
            if (strlen($field['value']) < 3) {
                $field['messages'] = 'Password should be at least 3 characters long...';
                return false;
            }
            return true;
        });
    }

    if ($csi->status() !== 'success') {
        return $csi;
    }

    return i18n::create_cache('mysli/cms/dash')
    && users::create(
        ['email' => $csi->get('email'), 'password' => $csi->get('password')])
    && event::register(
        'mysli/web/web:route<*><dashboard*>', 'mysli\\cms\\dash\\dash::run');
}

function disable() {
    return i18n::remove_cache('mysli/cms/dash')
    && event::unregister(
        'mysli/web/web:route<*><dashboard*>', 'mysli\\cms\\dash\\dash::run');
}
