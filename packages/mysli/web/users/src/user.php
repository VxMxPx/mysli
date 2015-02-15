<?php

namespace mysli\web\users;

__use(__namespace__, '
    mysli.framework.exception/*  AS  framework\exception\*
    mysli.framework.json
    mysli.framework.fs/fs,file
    mysli.util.config
    mysli.web.html
');

class user {

    // When the email is changed, ID and filename must be changed with it.
    // This variable be set to true until ->save() is called.
    private $modified_email = false;
    private $properties = [
        'id'           => null,
        'email'        => null,
        'password'     => null,
        'name'         => null,
        'last_seen_on' => null,
        'updated_on'   => null,
        'created_on'   => null,
        'deleted_on'   => null,
        'is_active'    => null,
    ];
    // config object
    private $config;

    /**
     * Create new User object, ...
     * @param array   $record   See properties above for possible elements.
     * @param boolean $validate Set data by calling methods.
     */
    function __construct(array $record=[], $validate=false) {

        foreach ($this->properties as $k => $v) {
            if (isset($record[$k])) {
                if ($validate) {
                    $this->{"set_{$k}"}($record[$k]);
                } else {
                    $this->properties[$k] = $record[$k];
                }
            }
        }

        if (isset($record['config'])) {
            $this->set_config($record['config']);
        }

        $this->config = config::select('mysli/web/users/uid_'.$this->id);
    }
    /**
     * @param  string $property
     */
    function __get($property) {
        if (method_exists($this, "get_{$property}")
        && array_key_exists($property, $this->properties)) {
            return call_user_func([$this, "get_{$property}"]);
        }
        if ($property === 'config') {
            return $this->config->as_array();
        }
    }
    /**
     * @param string $property
     * @param mixed  $value
     */
    function __set($property, $value) {
        if (method_exists($this, "set_{$property}")
        && array_key_exists($property, $this->properties)) {
            return call_user_func_array([$this, "set_{$property}"], [$value]);
        }
        if ($property === 'config') {
            if (is_array($value)) {
                return $this->set_config($value);
            } else {
                throw new framework\exception\argument(
                    "Config accept only array. ".
                    "Use `set_config(string key, mixed value)` ".
                    "to set particular config key.");
            }
        }
    }
    /**
     * Return user's ID.
     * @return string
     */
    function get_id() {
        return $this->properties['id'];
    }
    /**
     * Throw exception if trying to set id.
     */
    private function set_id() {
        throw new framework\exception\argument(
            "Cannot change ID, it's automatically generated from email.");
    }
    /**
     * Get e-mail addres.
     * @return string
     */
    function get_email() {
        return $this->properties['email'];
    }
    /**
     * Set e-mail addres.
     * @param  string $email
     */
    function set_email($email) {
        $email = trim($email);

        if ($email === $this->email) {
            return;
        }

        if (mb_strlen($email) < 3 || !strpos($email, '@')) {
            throw new framework\exception\argument(
                "Invalid e-mail address: `{$email}`", 1);
        }

        if (users::exists(users::get_id_from_uname($email))) {
            throw new framework\exception\argument(
                "User already exists: `{$email}`", 2);
        }

        $this->modified_email = $email;
    }
    /**
     * Check if password (plain) match saved hash.
     * @param  string $password
     * @return boolean
     */
    function auth_password($password) {
        if (function_exists('password_verify')) {
            return password_verify($password, $this->password);
        } else {
            return crypt($password, $this->password) === $this->password;
        }
    }
    /**
     * Get new password.
     * @return string
     */
    function get_password() {
        return $this->properties['password'];
    }
    /**
     * Set new password.
     * @param  string $value
     * @return string
     */
    function set_password($password) {
        if (function_exists('password_hash')) {
            $this->properties['password'] = password_hash($password,
                                                            PASSWORD_DEFAULT);
        } else {
            $this->properties['password'] = crypt($password);
        }
    }
    /**
     * Get user's real name
     * @return string
     */
    function get_name() {
        return $this->properties['name'];
    }
    /**
     * Set user's real name
     * @param string $name
     */
    function set_name($name) {
        $this->properties['name'] = html::strip_tags($name);
    }
    /**
     * Get last seen on
     * @return integer
     */
    function get_last_seen_on() {
        return $this->properties['last_seen_on'];
    }
    /**
     * Set last seen on. In format YmdHis
     * @param integer $date
     */
    function set_last_seen_on($date) {
        $this->properties['last_seen_on'] = (int) $date;
    }
    /**
     * Get updated
     * @return integer
     */
    function get_updated_on() {
        return $this->properties['updated_on'];
    }
    /**
     * Set updated. In format YmdHis
     * @param integer $date
     */
    function set_updated_on($date) {
        $this->properties['updated_on'] = (int) $date;
    }
    /**
     * Get created
     * @return integer
     */
    function get_created_on() {
        return $this->properties['created_on'];
    }
    /**
     * Set created. In format YmdHis
     * @param integer $date
     */
    function set_created_on($date) {
        $this->properties['created_on'] = (int) $date;
    }
    /**
     * Get deleted on date. In format YmdHis
     * @return integer null if not deleted
     */
    function get_deleted_on() {
        return $this->properties['deleted_on'];
    }
    /**
     * Delete current user.
     * @return boolean
     */
    function delete() {
        $this->properties['deleted_on'] = gmdate('YmdHis');
    }
    /**
     * Return current user's delete state
     * @return boolean
     */
    function is_deleted() {
        return !!$this->properties['deleted_on'];
    }
    /**
     * This will hard-delete user, - e.g. delete user's record permanently.
     * @return boolean
     */
    function destroy() {
        $id = $this->id;
        foreach ($this->properties as $p) {
            $this->properties[$p] = null;
        }
        return file::remove(users::path_by_id($id))
            && $this->config->destroy();
    }
    /**
     * Weather user is active.
     * @return boolean
     */
    function get_is_active() {
        return $this->properties['is_active'];
    }
    /**
     * Set if active state
     * @param boolean $state
     */
    function set_is_active($state) {
        $this->properties['is_active'] = !!$state;
    }
    /**
     * Set configuration item
     * @param mixed $key   string to set one config key, array to set multiple
     * @param mixed $value
     */
    function set_config($key, $value=null) {
        if (is_array($key)) {
            $this->config->merge($key);
        } else {
            $this->config->set($key, $value);
        }
    }
    /**
     * Get configuration item
     * @return mixed
     */
    function get_config($key) {
        return $this->config->get($key);
    }
    /**
     * Save changes made in this user object.
     * @return boolean
     */
    function save() {
        if ($this->modified_email) {
            users::delete($this->id);
            $this->properties['id'] = users::get_id_from_uname(
                                                        $this->modified_email);
            $this->properties['email'] = $this->modified_email;
            $this->modified_email = false;
        }

        $filename = users::path_by_id($this->id);

        return $this->config->save()
            && json::encode_file($filename, $this->properties);
    }
}
