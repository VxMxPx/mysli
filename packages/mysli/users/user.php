<?php

namespace Mysli\Users;

class User
{
    protected $path;
    protected $properties = [
        'email'         => '',
        'password'      => '',
        'salt'          => '',
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
     * --
     * @param string $path   The path (including filename) where user
     *                       will be saved to or loaded from.
     * @param array  $record See properties above for possible elements.
     *                       If empty, the user will be loaded from $path.
     */
    public function __construct($path, array $record = [])
    {
        if (empty($record)) {
            $record = \Core\JSON::decode_file($path, true);
            $raw = true; // Will not process values when assigning them.
        } else {
            // New user being created...
            $record['created_on'] = gmdate('YmdHis');
            $record['updated_on'] = gmdate('YmdHis');
            $raw = false;
        }

        $this->path = $path;

        foreach ($this->properties as $k => $v) {
            if (isset($record[$k])) {
                if ($raw === true) {
                    $this->properties[$k] = $record[$k];
                } else {
                    if ($k !== 'settings') {
                        $this->{$k}($record[$k]);
                    } else {
                        $this->{$k}(null, $record[$k]);
                    }
                }
            }
        }
    }

    /**
     * Dump data as array.
     * --
     * @return array
     */
    public function as_array()
    {
        return $this->properties;
    }

    /**
     * Check if password is correct.
     * --
     * @param  string $password
     * --
     * @return boolean
     */
    public function auth_password($password)
    {
        return $this->password() === $this->generate_password($password, $this->salt());
    }

    /**
     * Return user's ID.
     * --
     * @return string
     */
    public function id()
    {
        return md5($this->properties['email']);
    }

    /**
     * This will deactivate this account.
     * --
     * @return void
     */
    public function deactivate()
    {
        $this->properties['is_active'] = false;
    }

    /**
     * This will activate this account.
     * --
     * @return void
     */
    public function activate()
    {
        $this->properties['is_active'] = true;
    }

    /**
     * Is this user's account active? (Not deleted, etc...)
     * --
     * @return boolean
     */
    public function is_active()
    {
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
     * --
     * @param  string $value
     * --
     * @return string
     */
    public function uname($value = null)
    {
        return $this->email($value);
    }

    /**
     * Get / set e-mail addres.
     * --
     * @param  mixed $value
     * --
     * @throws \Core\ValueException If Invalid email when setting (no @ or less than 3 chars)
     * --
     * @return string
     */
    public function email($value = null)
    {
        if ($value !== null) {
            $value = trim($value);
            if (mb_strlen($value) < 3 || strpos($value, '@') === false) {
                throw new \Core\ValueException("Invalid e-mail addes.", 1);
            }
            $this->properties['email'] = $value;
        }

        return $this->properties['email'];
    }

    /**
     * Set / get salt. If $value is true, salt will be auto generated.
     * --
     * @param  mixed $value String / boolean (true)
     * --
     * @return string
     */
    public function salt($value = null)
    {
        if ($value !== null) {
            if ($value === true) {
                $value = \Core\Str::random(8);
            }
            $this->properties['salt'] = $value;
        }

        return $this->properties['salt'];
    }


    /**
     * Set / get new password.
     * --
     * @param  string $value
     * --
     * @return string
     */
    public function password($value = null)
    {
        if ($value !== null) {
            $salt = $this->salt(true);
            $this->properties['password'] = $this->generate_password($value, $salt);
        }

        return $this->properties['password'];
    }

    /**
     * Set / get settings
     * @param  string $key  If string, and no $value, then, that element will be
     *                      returned from settings. If $key and $value,
     *                      then the key will be updated / created.
     * @param  mixed $value If empty, $key will be returned, else $key will be set.
     *                      If array, and key null, $value will be merged with all
     *                      settings.
     * --
     * @return mixed String or array.
     */
    public function settings($key = null, $value = null)
    {
        if ($key === null) {
            if (is_array($value)) {
                $this->properties['settings'] = array_merge(
                    $this->properties['settings'],
                    $value
                );
            }
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
     * --
     * @return boolean
     */
    public function save()
    {
        return \Core\JSON::encode_file($this->path, $this->properties);
    }

    /**
     * Mark this user as deleted.
     * --
     * @return boolean
     */
    public function delete()
    {
        $this->properties['deleted_on'] = gmdate('YmdHis');
        return $this->save();
    }

    /**
     * Generate the password.
     * --
     * @param  string $password
     * @param  string $salt
     * --
     * @return string
     */
    protected function generate_password($password, $salt)
    {
        // p[assword] t[ype] m[ysli] [version] 0 f[in]
        return 'ptm0f' . sha1( sha1( sha1( $password ) . sha1( $salt ) ) );
    }

    /**
     * Call inaccessible methods.
     * --
     * @param  string $name      Method's name
     * @param  array  $arguments Method's arguments
     * --
     * @throws \Core\ValueException If property doesn't exists.
     * --
     * @return void
     */
    public function __call($name, array $arguments)
    {
        if (isset($this->properties[$name])) {
            if (!empty($arguments)) {
                $this->properties[$name] = $arguments[0];
            }
            return $this->properties[$name];
        } else {
            throw new \Core\ValueException("No such property: `{$name}`", 1);
        }
    }
}
