<?php

namespace mysli\web\users;

class user {

    protected $properties = [
        'id'            => '',
        'email'         => '',
        'password'      => '',
        'name'          => '',
        'last_seen_on'  => '',
        'updated_on'    => '',
        'created_on'    => '',
        'is_active'     => true,
        'deleted_on'    => false,
        'is_super'      => false,
        'settings'      => []
    ];

    /**
     * Create new User object, ...
     * @param array   $record   See properties above for possible elements.
     * @param boolean $validate Set data by calling methods.
     */
    function __construct(array $record=[], $validate=false) {

        foreach ($this->properties as $k => $v) {
            if (isset($record[$k])) {
                if ($validate) {
                    if ($k !== 'settings') {
                        $this->{$k}($record[$k]);
                    } else {
                        $this->{$k}($record[$k]);
                    }
                } else {
                    $this->properties[$k] = $record[$k];
                }
            }
        }
    }
    /**
     * Dump data as array.
     * @return array
     */
    function as_array() {
        return $this->properties;
    }
    /**
     * Return user's ID.
     * @return string
     */
    function id() {
        return $this->properties['id'];
    }
    /**
     * Deactivate the account.
     */
    function deactivate() {
        $this->properties['is_active'] = false;
    }
    /**
     * Activate this account.
     */
    function activate() {
        $this->properties['is_active'] = true;
    }
    /**
     * Is this user's account active? (Not deleted, etc...)
     * @return boolean
     */
    function is_active() {
        // User's password must be set in order account to be valid.
        if (!$this->properties['password']) return false;

        // Account shoulnd't be deleted.
        if ($this->properties['deleted_on']) return false;

        // is_active property shouldn't be false.
        if (!$this->properties['is_active']) return false;

        // If all the above passed, then account is active.
        return true;
    }
    /**
     * Alias for uname, in some cases uname might be different than e-mail.
     * @param  string $value
     * @return string
     */
    function uname($value=null) {
        return $this->email($value);
    }
    /**
     * Get / set e-mail addres.
     * @param  string $email
     * @return string
     */
    function email($email=null) {

        if ($email !== null) {
            $email = trim($email);
            if ($email === $this->email()) {
                return $email;
            }
            if (mb_strlen($email) < 3 || !strpos($email, '@')) {
                throw new framework\exception\argument(
                    "Invalid e-mail addes: `{$email}`", 1);
            }
            if (users::exists(users::get_id_from_uname($email))) {
                throw new framework\exception\argument(
                    "User already exists: `{$email}`", 2);
            }
            $this->properties['email'] = $email;
        }

        return $this->properties['email'];
    }
    /**
     * Check if password (plain) match saved hash.
     * @param  string $password
     * @return boolean
     */
    function auth_password($password) {
        if (function_exists('password_verify')) {
            return password_verify($password, $this->password());
        } else {
            return crypt($password, $this->password()) === $this->password();
        }
    }
    /**
     * Set / get new password.
     * @param  string $value
     * @return string
     */
    function password($value=null) {

        if ($value !== null) {
            $this->properties['password'] = $this->generate_password($value);
        }

        return $this->properties['password'];
    }
    /**
     * Set / get settings
     * @param  mixes  $key  string, and no $value, element will be returned
     *                      from settings. If $key and $value, the key will be
     *                      updated / created.
     *                      array: multiple elements will be updated
     * @param  mixed $value empty, $key will be returned, else $key will be set.
     * @return mixed string or array
     */
    function settings($key=null, $value=null) {

        if ($key === null) {
            return $this->properties['settings'];
        }

        if (is_array($key)) {
            $this->properties['settings'] = arr::merge(
                                                $this->properties['settings'],
                                                $key);
            return $this->properties['settings'];
        }

        if ($value !== null) {
            $this->properties['settings'][$key] = $value;
        }

        if (isset($this->properties['settings'][$key])) {
            return $this->properties['settings'][$key];
        }
    }
    /**
     * Save changes made in this user object.
     * @return boolean
     */
    function save() {
        return json::encode_file(
                        users::path_by_id($this->id()),
                        $this->properties);
    }
    /**
     * Mark this user as deleted.
     * @param  boolean $soft if false user file will be permanently removed
     * @return boolean
     */
    function delete($soft=true) {
        if ($soft) {
            $this->properties['deleted_on'] = gmdate('YmdHis');
            return true;
        } else {
            return file::remove(users::path_by_id($this->id()));
        }
    }
    /**
     * Set/get properties.
     * @param  string $name      Method's name
     * @param  array  $arguments Method's arguments
     */
    function __call($name, array $arguments) {

        if (isset($this->properties[$name])) {
            if (!empty($arguments)) {
                $this->properties[$name] = $arguments[0];
            }
            return $this->properties[$name];
        } else {
            throw new framework\exception\argument(
                "No such property: `{$name}`", 1);
        }
    }

    /**
     * Generate the password.
     * --
     * @param  string $password
     * --
     * @return string
     */
    protected function generate_password($password) {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        } else {
            return crypt($password);
        }
    }
}
