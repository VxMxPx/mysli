<?php

namespace Mysli;

class User
{
    protected static $current;

    protected $properties = [
        'email'      => '',
        'password'   => '',
        'salt'       => '',
        'full_name'  => '',
        'lastlogin'  => '',
        'updated'    => '',
        'settings'   => []
    ];

    public function __construct(array $record)
    {
        foreach ($this->properties as $k => $v) {
            if (isset($record[$k])) {
                $this->properties[$k] = $record[$k];
            }
        }
    }

    public static function current($set=false)
    {
        if ($set) {
            if (self::$current) {
                throw new Exception("Cannot set user again, already there!", 1);
            } else {
                self::$current = $set;
            }
        } else {
            return self::$current;
        }
    }

    public function dump()
    {
        return $this->properties;
    }

    public function save()
    {
        try {
            Users::update($this->properties, $this->email);
        } catch (Exception $e) {
            throw $e;
        }

        return Users::save();
    }

    public function __get($property) {
        if (isset($this->properties[$property])) {
            if (method_exists($this, 'get_' . $property)) {
                return call_user_func([$this, 'get_'.$property]);
            } else {
                return $this->properties[$property];
            }
        }
    }

    public function __set($property, $value) {
        if (isset($this->properties[$property])) {
            if (method_exists($this, 'set_' . $property)) {
                $this->properties[$property] = call_user_func_array([$this, 'set_'.$property], [$value]);
            } else {
                $this->properties[$property] = $value;
            }
        }
    }

    protected function set_salt($salt)
    {
        if ($salt === true) {
            $salt = Lib\Str::random(8);
        }

        return $salt;
    }

    protected function set_password($password)
    {
        $this->salt = true;
        return $this->gen_password($password);
    }

    public function auth_passwd($password)
    {
        return $this->password === $this->gen_password($password);
    }

    protected function gen_password($password)
    {
        return sha1( sha1( sha1( $password ) . sha1( $this->salt ) ) );
    }
}