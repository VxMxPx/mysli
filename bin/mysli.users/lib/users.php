<?php

namespace mysli\users; class users
{
    const __use = '
        .{ exception.users }
        mysli.toolkit.{ json }
        mysli.toolkit.fs.{ fs, dir, file }
    ';

    /**
     * Cached users objects.
     * --
     * @var array
     */
    protected static $cache = [];

    /**
     * Authenticate the user.
     * --
     * @param string $email
     * @param string $password
     * --
     * @return mixed False, or object mysli\users\user
     */
    static function auth($email, $password)
    {
        $user = static::get_by_uname($email);

        if (!$user)                           return false;
        if (!$user->auth_password($password)) return false;
        if (!$user->is_active)                return false;
        if (!$user->password)                 return false;
        if ($user->is_deleted)                return false;

        return $user;
    }

    /**
     * Get list of all users.
     * --
     * @param boolean $detailed
     *        If true each user's record will be an array containing basic
     *        user's details. Otherwise list of IDs will be returned.
     * --
     * @return array
     */
    static function get_all($detailed=false)
    {
        $users_files = fs::ls(fs::cntpath('users'), '/^.*?\.json$/');
        $users = [];

        foreach ($users_files as $user_file)
        {
            $id = substr($users_file, 0, -5); // -.json

            if ($detailed)
            {
                $filename = fs::cntpath("users/{$user_file}");
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
     * --
     * @param string $email
     * --
     * @return mysli\users\user or Null
     */
    static function get_by_uname($email)
    {
        // The id is just a md5 version of email...
        return static::get_by_id(static::get_id_from_uname($email));
    }

    /**
     * Get one user by ID.
     * --
     * @param string $id
     * --
     * @return mysli\users\user or Null
     */
    static function get_by_id($id)
    {
        if (!isset(static::$cache[$id]))
        {
            if (static::exists($id))
            {
                $u = new user(json::decode_file(static::path_by_id($id), true));
                static::$cache[$id] = $u;
            }
            else
            {
                return;
            }
        }

        return static::$cache[$id];
    }

    /**
     * Check weather user exists, by id.
     * --
     * @param string $id
     * --
     * @return boolean
     */
    static function exists($id)
    {
        return file::exists(static::path_by_id($id));
    }

    /**
     * Accept uname (email) and get qid.
     * --
     * @param string $email
     * --
     * @return string
     */
    static function get_id_from_uname($email)
    {
        return md5($email);
    }

    /**
     * This will hard delete the user (by id) the user exists.
     * --
     * @param string $id
     * --
     * @return boolean
     */
    static function delete($id)
    {
        $file = static::path_by_id($id);

        if (file::exists($file))
        {
            if (isset(static::$cache[$id]))
            {
                unset(static::$cache[$id]);
            }

            return file::remove($file);
        }
        else
        {
            return true;
        }
    }

    /**
     * Create new user and return mysli\users\user
     * --
     * @param mixed $user
     *        String, a valid e-mail address.
     *        Array, the `email` key should exists.
     * --
     * @throws mysli\users\exception\users 10 The `email` must be set.
     * --
     * @return mysli\users\user || Null if user already exists.
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
            throw new exception\users('The `email` must be set.', 10);
        }

        $id = static::get_id_from_uname($user['email']);

        // User already exists?
        if (static::exists($id))
        {
            return false;
        }

        // $user['id'] = $id;
        $user['is_active']  = true;
        $user['created_on'] = (int) gmdate('YmdHis');
        $user['updated_on'] = (int) gmdate('YmdHis');

        $user = new user($user, true);

        return ($user && (static::$cache[$id] = $user->save()));
    }

    /**
     * Get path for particular user's file,
     * --
     * @param string $id
     * --
     * @return string Path to user's file.
     */
    static function path_by_id($id)
    {
        return fs::cntpath("users/{$id}.json");
    }
}
