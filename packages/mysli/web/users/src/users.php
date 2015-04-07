<?php

namespace mysli\web\users;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
    mysli.framework.json
    mysli.framework.fs/fs,file
');

class users
{
    private static $cache = [];

    /**
     * Authenticate the user.
     * @param  string $email
     * @param  string $password
     * @return mixed  false, or object \mysli\users\user
     */
    static function auth($email, $password)
    {
        $user = self::get_by_uname($email);

        if (!$user)                           return false;
        if (!$user->auth_password($password)) return false;
        if (!$user->is_active)                return false;
        if (!$user->password)                 return false;
        if ($user->is_deleted)                return false;

        return $user;
    }
    /**
     * Get list of all users
     * @param  boolean $detailed if true, each user's record will be an array
     *                           including basic user's details. Otherwise
     *                           list of IDs will be returned.
     * @return array
     */
    static function get_all($detailed=false)
    {
        $users_files = fs::ls(fs::datpath('mysli/web/users'), '/^.*?\.json$/');
        $users = [];

        foreach ($users_files as $user_file)
        {
            $id = substr($users_file, 0, -5); // -.json

            if ($detailed)
            {
                $filename = fs::datpath("mysli/web/users/{$user_file}");
                $users[$id] = json::decode_file($filename, false);
            }
            else
            {
                $users[] = $id;
            }
        }

        return $users;
    }
    /**
     * Get one user by uname = e-mail.
     * @param  string $email
     * @return mixed  false, or object \mysli\web\users\user
     */
    static function get_by_uname($email)
    {
        // The id is just a md5 version of email...
        return self::get_by_id(self::get_id_from_uname($email));
    }
    /**
     * Get one user by ID.
     * @param  string  $id
     * @return mixed   false, or object \mysli\web\users\user
     */
    static function get_by_id($id)
    {
        if (!isset(self::$cache[$id]))
        {
            if (self::exists($id))
            {
                $u = new user(json::decode_file(self::path_by_id($id), true));
                self::$cache[$id] = $u;
            }
            else
            {
                return false;
            }
        }

        return self::$cache[$id];
    }
    /**
     * Check weather user exists, by id
     * @param  string $id
     * @return boolean
     */
    static function exists($id)
    {
        return file::exists(self::path_by_id($id));
    }
    /**
     * Accept uname (email) and get qid
     * @param  string $email
     * @return string
     */
    static function get_id_from_uname($email)
    {
        return md5($email);
    }
    /**
     * This will hard delete the user (by id) the user exists.
     * @param  string $id
     * @return boolean
     */
    static function delete($id)
    {
        $file = self::path_by_id($id);

        if (file::exists($file))
        {
            if (isset(self::$cache[$id]))
            {
                unset(self::$cache[$id]);
            }

            return file::remove($file);
        }
        else
        {
            return true;
        }
    }
    /**
     * Create new user and return \mysli\web\users\user
     * @param  mixed $user If string, it should be valid e-mail address,
     *                     if array, then the `email` key should exists.
     * @return mixed \mysli\web\users\user or false if user already exists.
     */
    static function create($user)
    {
        if (!is_array($user))
        {
            $user = [
                'email' => $user
            ];
        }
        elseif (!isset($user['email']))
        {
            throw new framework\exception\argument('The `email` must be set.');
        }

        $id = self::get_id_from_uname($user['email']);

        // User already exists?
        if (self::exists($id))
        {
            return false;
        }

        // $user['id'] = $id;
        $user['is_active']  = true;
        $user['created_on'] = (int) gmdate('YmdHis');
        $user['updated_on'] = (int) gmdate('YmdHis');

        $user = new user($user, true);

        return ($user && (self::$cache[$id] = $user->save()));
    }
    /**
     * Get path for particular user's file,
     * @param  string $id
     * @return string Path to user's file.
     */
    static function path_by_id($id)
    {
        return fs::datpath("mysli/web/users/{$id}.json");
    }
}
