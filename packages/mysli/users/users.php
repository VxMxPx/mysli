<?php

namespace Mysli\Users;

class Users
{
    use \Mysli\Core\Pkg\Singleton;

    protected $path;     // Path where the users are stored!
    protected $cache;    // Collection of all users objects (returned as reference!)

    public function __construct()
    {
        $this->path = datpath('mysli.users');
    }

    /**
     * Authenticate the user.
     * --
     * @param  string $email
     * @param  string $password
     * --
     * @return mixed  false, or object \Mysli\Users\User
     */
    public function auth($email, $password)
    {
        $user = $this->get_by_uname($email);

        if (!$user)                           return false;
        if (!$user->auth_password($password)) return false;
        if (!$user->is_active())              return false;

        return $user;
    }

    /**
     * Get path for particular user's file,
     * --
     * @param  string  $id
     * --
     * @return string Path to user's file.
     */
    public function get_path($id)
    {
        return ds($this->path, $id . '.json');
    }

    /**
     * Get one user by uname = e-mail.
     * --
     * @param  string $email
     * --
     * @return mixed  false, or object \Mysli\Users\User
     */
    public function get_by_uname($email)
    {
        // The id is just a md5 version of email...
        return $this->get_by_id(md5($email));
    }

    /**
     * Get one user by ID.
     * --
     * @param  string  $id
     * --
     * @return mixed   false, or object \Mysli\Users\User
     */
    public function get_by_id($id)
    {
        $path = $this->get_path($id);
        if (file_exists($path)) {
            if (isset($this->cache[$id])) {
                return $this->cache[$id];
            } else {
                return ( $this->cache[$id] = new \Mysli\Users\User($path) );
            }
        } else {
            return false;
        }
    }

    /**
     * Create new user and return \Mysli\Users\User
     * --
     * @param  mixed $data If string, it should be valid e-mail address,
     *                     if array, then the `email` key should exists.
     * --
     * @return mixed object \Mysli\Users\User or False if user already exists.
     */
    public function create($data)
    {
        if (!is_array($data)) {
            $data  = [
                'email' => $data
            ];
        } else if (!isset($data['email'])) {
            throw new \Core\ValueException(
                'The e-mail property is required.', 1
            );
        }

        $id = md5($data['email']);
        $path = $this->get_path($id);

        // User already exists?
        if ( file_exists($path) ) {
            return false;
        }

        return ( $this->cache[$id] = new \Mysli\Users\User($path, $data) );
    }

    /**
     * Delete users by ID or uname (email).
     * --
     * @param  array   $users List of users' IDs or unames (email).
     * --
     * @return integer        Number of deleted records.
     */
    public function delete(array $users)
    {
        $deleted = 0;
        foreach ($users as $id) {
            if (strpos($id, '@') !== false) {
                $user = $this->get_by_uname($id);
            } else {
                $user = $this->get_by_id($id);
            }
            if (!$user) { continue; }
            $user->delete();
            $deleted++;
        }
        return $deleted;
    }
}
