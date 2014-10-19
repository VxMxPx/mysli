<?php

namespace mysli\web\users;

class users {

    private static $cache = [];

    /**
     * Authenticate the user.
     * @param  string $email
     * @param  string $password
     * @return mixed  false, or object \mysli\users\user
     */
    static function auth($email, $password) {

        $user = self::get_by_uname($email);

        if (!$user)                           return false;
        if (!$user->auth_password($password)) return false;
        if (!$user->is_active())              return false;

        return $user;
    }
    /**
     * Get one user by uname = e-mail.
     * @param  string $email
     * @return mixed  false, or object \mysli\users\user
     */
    static function get_by_uname($email) {
        // The id is just a md5 version of email...
        return self::get_by_id(self::get_id_from_uname($email));
    }
    /**
     * Get one user by ID.
     * @param  string  $id
     * @return mixed   false, or object \mysli\users\user
     */
    static function get_by_id($id) {

        if (!isset(self::$cache[$id])) {
            if (self::exists($id)) {
                $u = new user(json::decode_file(self::path_by_id($id), false));
                self::$cache[$id] = $u;
            } else {
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
    static function exists($id) {
        return file::exists(self::path_by_id($id));
    }
    /**
     * Accept uname (email) and get qid
     * @param  string $email
     * @return string
     */
    static function get_id_from_uname($email) {
        return md5($email);
    }
    /**
     * Create new user and return \mysli\users\user
     * @param  mixed $user If string, it should be valid e-mail address,
     *                     if array, then the `email` key should exists.
     * @return mixed \mysli\users\user or false if user already exists.
     */
    static function create($user) {

        if (!is_array($user)) {
            $user = [
                'email' => $user
            ];
        } elseif (!isset($user['email'])) {
            throw new framework\exception\argument(
                'The e-mail property is required.');
        }

        $id = get_id_from_uname($user['email']);

        // User already exists?
        if (self::exists($id)) {
            return false;
        }

        $user['id'] = $id;
        $user['created_on'] = (int) gmdate('YmdHis');
        $user['updated_on'] = (int) gmdate('YmdHis');

        return (self::$cache[$id] = new user($user, true));
    }
    // /**
    //  * Delete user by ID or uname (email).
    //  * @param  string  $user user name or email address.
    //  * @param  boolean $soft weather user should be soft-deleted
    //  * @return boolean
    //  */
    // static function delete($user, $soft=true) {

    //     if (strpos($user, '@')) {
    //         $user = self::get_id_from_uname($user);
    //     }

    //     if ($soft) {
    //         $user = self::get_by_id($user);
    //         unset(self::$cache[$user]);
    //         $user->delete();
    //         return $user->save();
    //     } else {
    //         if (isset(self::$cache[$user])) {
    //             unset(self::$cache[$user]);
    //         }
    //         $filename = self::path_by_id($user);
    //         if (file::exists($filename)) {
    //             return file::remove($filename);
    //         }
    //     }
    // }
    /**
     * Get path for particular user's file,
     * @param  string $id
     * @return string Path to user's file.
     */
    static function path_by_id($id) {
        return fs::datpath("mysli/web/users/{$id}.json");
    }
}
