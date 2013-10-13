<?php

namespace Mysli;

class Users
{
    protected static $filename;
    protected static $users;

    public static function init($filename)
    {
        if (!file_exists($filename)) {
            trigger_error("File not found: `{$filename}`", E_USER_ERROR);
        }

        self::$filename = $filename;
        self::reload();
    }

    public static function update($record, $id)
    {
        self::reload();

        if (isset(self::$users[$id])) {
            self::$users[$id] = $record;
        } else {
            throw new Exception("Cannot find user with id: `{$id}`.", 1);
        }

        return self::write();
    }

    public static function write()
    {
        return file_put_contents(self::$filename, json_encode(self::$users));
    }

    public static function get_by_email($email)
    {
        if (isset(self::$users[$email])) {
            return new User(self::$users[$email]);
        } else {
            return false;
        }
    }

    public static function reload()
    {
        self::$users = json_decode(file_get_contents(self::$filename), true);
    }

    public static function auth($email, $password)
    {
        $user = self::get_by_email($email);

        if (!$user) {
            return false;
        }

        if (!$user->auth_passwd($password)) {
            return false;
        }

        return $user;
    }

}