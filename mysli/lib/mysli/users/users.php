<?php

namespace Mysli;

class Users
{
    protected $config;   // This library's configuration
    protected $core;     // The core (dependency)

    protected $path;     // Path where the users are stored!
    protected $cache;    // Collection of all users objects (returned as reference!)

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->config = $config;
        $this->core = $dependencies['core'];

        $this->path = datpath('users');
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
        $user = $this->get_by_email($email);

        if (!$user) {
            return false;
        }

        if (!$user->auth_passwd($password)) {
            return false;
        }

        return $user;
    }

    /**
     * Get path for particular user's file,
     * --
     * @param  string $email
     * --
     * @return string Path to user's file.
     */
    public function get_path($email)
    {
        return ds($this->path, md5($email) . '.json');
    }

    /**
     * Get one user by e-mail.
     * --
     * @param  string $email
     * --
     * @return mixed  false, or object \Mysli\Users\User
     */
    public function get_by_email($email)
    {
        $path = $this->get_path($email);
        if (file_exists($path)) {
            if (isset($this->cache[$email])) {
                return $this->cache[$email];
            } else {
                return ( $this->cache[$email] = new \Mysli\Users\User($path) );
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

        $path = $this->get_path($data['email']);

        // User already exists?
        if ( file_exists($path) ) {
            return false;
        }

        return ( $this->cache[$data['email']] = new \Mysli\Users\User($path, $data) );
    }

    /**
     * Delete users by email address.
     * --
     * @param  array   $users List of users' email addressed to be deleted.
     * --
     * @return integer        Number of deleted records.
     */
    public function delete(array $users)
    {
        $deleted = 0;
        foreach ($users as $user_email) {
            $user = $this->get_by_email($user_email);
            if (!$user) { continue; }
            $user->delete();
            $deleted++;
        }
        return $deleted;
    }
}
