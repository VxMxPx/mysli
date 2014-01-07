<?php

namespace Mysli\Users;

class User
{
    protected $properties = [
        'email'         => '',
        'password'      => '',
        'salt'          => '',
        'name'          => '',
        'last_seen_on'  => '',
        'updated_on'    => '',
        'created_on'    => '',
        'deleted_on'    => false,
        'is_super'      => false,
        'settings'      => []
    ];

    /**
     * Create new User object, ...
     * --
     * @param array    $record See properties above for possible elements.
     * @param boolean  $raw    When true, methods won't be set through setters,
     *                         but rather directly. Meaning, for example:
     *                         `password` field will not be hashed but set as it is.
     */
    public function __construct(array $record, $raw = false)
    {
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
                $value = \Str::random(8);
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
