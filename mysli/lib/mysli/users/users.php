<?php

namespace Mysli;

class Users
{
    protected $config;   // This library's configuration
    protected $core;     // The core (dependency)

    protected $filename; // File where users are saved (users/users.json)
    protected $users;    // Collection of all users from users/users.json

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->config = $config;
        $this->core = $dependencies['core'];

        $this->filename = datpath('users/users.json');

        // Load users file
        $this->reload();
    }

    /**
     * Load users again (from users/users.json)
     * --
     * @return void
     */
    public function reload()
    {
        $this->users = \JSON::decode_file($this->filename, true);
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
     * Get one user by e-mail.
     * --
     * @param  string $email
     * --
     * @return mixed  false, or object \Mysli\Users\User
     */
    public function get_by_email($email)
    {
        if (isset($this->users[$email])) {
            return new \Mysli\Users\User($this->users[$email], true);
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
            $email = $data;
            $data  = [
                'email' => $email
            ];
        } else if (isset($data['email'])) {
            $email = $data['email'];
        } else {
            throw new \Core\ValueException(
                `The e-mail property is required.`, 1
            );
        }

        // If user already exists, then false will be returned...
        if (isset($this->users[$email])) {
            return false;
        }

        $data['created_on'] = gmdate('YmdHis');
        $data['updated_on'] = gmdate('YmdHis');
        $this->users[$email] = $data;
        $this->write();

        return new \Mysli\Users\User($data, false);
    }

    /**
     * Update particular user.
     * --
     * @param  object $user \Mysli\Users\User
     * --
     * @return boolean
     */
    public function save(\Mysli\Users\User $user)
    {
        $this->reload();

        $email = $user->email();

        if (isset($this->users[$email])) {
            $user->updated_on = gmdate('YmdHis');
            $this->users[$email] = $user->as_array();
        } else {
            throw new \Core\ValueException(
                "Cannot find user with id: `{$email}`.", 1
            );
        }

        return $this->write();
    }

    /**
     * Delete user (by email) address.
     * --
     * @param  mixed   $id     String: email address
     *                         Object: instance of \Mysli\Users\User
     *                         Array:  collection of (see above) emails or objects.
     * @param  boolean $write  Write changes to file.
     * --
     * @return integer         Number of deleted records.
     */
    public function delete($id, $write = true)
    {
        if ($write) $this->reload();

        if (is_array($id)) {
            foreach ($id as $user) {
                $count += $this->delete($user, false);
            }
            if ($write) $this->write();
            return $count;
        }

        $deleted_on_timestamp = gmdate('YmdHis');

        if (is_object($id) && $id instanceof \Mysli\Users\User) {
            $id->deleted_on($deleted_on_timestamp);
            $id = $id->email();
        }

        if (isset($this->users[$id])) {
            $this->users[$id]['deleted_on'] = $deleted_on_timestamp;
        } else {
            return 0;
        }

        if ($write) $this->write();
        return 1;
    }

    /**
     * Write $this->users to file (users/users.json)
     * --
     * @return boolean
     */
    protected function write()
    {
        return \JSON::encode_file($this->filename, $this->users);
    }
}
