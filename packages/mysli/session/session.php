<?php

namespace Mysli;

class Session
{
    protected $config;
    protected $cookie;
    protected $users;

    protected $path;

    protected $user; // Currently discovered session // User Object!
    protected $info; // Current session information, like ID, etc...

    public function __construct($config, $cookie, $users)
    {
        $this->config = $config;
        $this->cookie = $cookie;
        $this->users  = $users;

        $this->path = datpath('session');

        $this->discover();
    }

    /**
     * Get current user.
     * --
     * @return mixed Object or null if no user is set.
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Current session information, like ID, etc...
     * --
     * @return mixed Array or null session is not set.
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * Return user's agent, if $hash is true, then it will md5 the agent,
     * so that it can be used as an unique identifier.
     * --
     * @param   boolean  $hash
     * --
     * @return  string
     */
    protected function get_agent($hash = true)
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if ($hash) {
            $agent = md5($agent);
        }
        return $agent;
    }

    /**
     * Get session's filename from id.
     * --
     * @param string $id
     * --
     * @return string
     */
    protected function get_filename_from_id($id)
    {
        return ds($this->path, $id . '_session.json');
    }

    /**
     * Will look for cookie, if anything found $this->user will be set!
     * --
     * @return boolean
     */
    public function discover()
    {
        // Check if we can find session id in cookies.
        if ( ! ($session_id = $this->cookie->read($this->config->get('cookie_name'))) ) {
            // $this->logger->info('No session found.', __FILE__, __LINE__);
            return false;
        }

        // Clean session id
        $session_id = \Core\Str::clean($session_id, 'aA1', '_');

        // Does such session_id exists?
        $session_path = $this->get_filename_from_id($session_id);
        if ( ! file_exists($session_path) ) {
            // $this->logger->info(
            //     "Session found in cookies but not in database: `{$session_id}`.",
            //     __FILE__, __LINE__
            // );
            // Remove cookie!
            $this->destroy($session_id);
            return false;
        }

        // Read session file
        $session = \Core\JSON::decode_file($session_path, true);
        if (!is_array($session)) {
            // $this->logger->warn(
            //     'Corrupted file for session: `' .
            //     $session_id . '`, containing: ' .
            //     print_r($session, true),
            //     __FILE__, __LINE__
            // );
            $this->destroy($session_id);
            return false;
        }

        // Is it expired?
        if ((int) $session['expires_on'] < time()) {
            // $this->logger->info(
            //     'Session found, but it\'s expired.',
            //     __FILE__, __LINE__
            // );
            $this->destroy($session_id);
            return false;
        }

        // Do we need identical IP address?
        if ($this->config->get('require_ip')) {
            if ($session['ip'] !== $_SERVER['REMOTE_ADDR']) {
                // $this->logger->info(
                //     "The session's IP: `{$session['ip']}`, " .
                //     "is not the same as the actual IP: `{$_SERVER['REMOTE_ADDR']}`.",
                //     __FILE__, __LINE__
                // );
                $this->destroy($session_id);
                return false;
            }
        }

        // Do we need identical agent?
        if ($this->config->get('require_agent')) {
            $current_agent = $this->get_agent();
            if ($session['agent'] !== $current_agent) {
                // $this->logger->info(
                //     "The agent set in session: `{$session['agent']}`, " .
                //     "doesn't match with the actual agent: `{$current_agent}`.",
                //     __FILE__, __LINE__
                // );
                $this->destroy($session_id);
                return false;
            }
        }

        // Get user finally...
        $user = $this->users->get_by_id($session['user_id']);
        if (!$user) {
            // $this->logger->info(
            //     "No user with such id: `{$session['user_id']}`.",
            //     __FILE__, __LINE__
            // );
            $this->destroy($session_id);
            return false;
        }

        if (!$user->is_active()) {
            // $this->logger->info(
            //     "User's account is not active: `{$session['user_id']}`.",
            //     __FILE__, __LINE__
            // );
            $this->destroy($session_id);
            return false;
        }

        // $this->logger->info(
        //     "Session was found for `{$session['user_id']}`, user will be set!",
        //     __FILE__, __LINE__
        // );

        $this->user = $user;
        $this->info = $session;
        $this->renew($session_id, $session, $user);
        return true;
    }

    /**
     * Create session (set cookie, etc...)
     * --
     * @param   object  $user    Valid user object.
     * @param   boolean $expires Null for default
     *                           init costume expiration in seconds,
     *                           0 to expires when browser is closed.
     * --
     * @return  boolean
     */
    public function create($user, $expires = null)
    {
        if (!method_exists($user, 'id')) {
            throw new \Core\ValueException(
                "Invalid user object! Required method `id` not available.", 1
            );
        }

        // Set expires to some time in future. It 0 was set in config, then we
        // set it to expires immediately when browser window is closed.
        if ($expires === null) {
            $expires = (int) $this->config->get('expires');
            $expires = $expires > 0 ? $expires + time() : 0;
        } else {
            $expires = (int) $expires;
        }

        // Create a unique id
        $id  = time() . '_' . \Core\Str::random(20, 'aA1');

        // Store cookie
        $this->cookie->create($this->config->get('cookie_name'), $id, $expires);

        // Set session file
        $session = [
            'id'         => $id,
            'user_id'    => $user->id(),
            'expires_on' => $expires === 0 ? time() + 60 * 60 : $expires,
            'ip'         => $_SERVER['REMOTE_ADDR'],
            'agent'      => $this->get_agent(),
        ];

        $this->user = $user;
        $this->info = $session;
        return $this->write($id, $session);
    }

    /**
     * Renew session (extend timeout, etc...)
     * --
     * @param  string  $id
     * @param  array   $session
     * @param  object  $user
     * @param  boolean $expires Null for default or costume expiration in seconds,
     *                           0, to expires when browser is closed.
     * --
     * @return boolean
     */
    protected function renew($id, array $session, $user, $expires = null)
    {
        // If we have to change id on renew,
        // then we'll destroy current session and set new one.
        if ($this->config->get('change_id_on_renew')) {
            $this->destroy();
            $this->user = $user;
            return $this->create($user);
        }

        // Set expires to some time in future. It 0 was set in config, then we
        // set it to expires immediately when browser window is closed.
        if ($expires === null) {
            $expires = (int) $this->config->get('expires');
            $expires = $expires > 0 ? $expires + time() : 0;
        } else {
            $expires = (int) $expires;
        }

        $session['expires_on'] = $expires === 0 ? time() + 60 * 60 : $expires;
        $session['ip']         = $_SERVER['REMOTE_ADDR'];
        $session['agent']      = $this->get_agent();

        $this->cookie->create($this->config->get('cookie_name'), $id, $expires);

        return $this->write($id, $session);
    }

    /**
     * Will clear all expired sessions, and return the amount of removed items.
     * --
     * @return  integer
     */
    public function cleanup()
    {
        $removed = 0;
        $sessions = scandir($this->path);

        foreach ($sessions as $session_file)
        {
            if (mb_substr($session_file, -13) !== '_session.json') continue;
            $filename = ds($this->path, $session_file);
            $session = \Core\JSON::decode_file($filename);
            if ($session['expires_on'] < time()) {
                \Core\FS::file_remove($filename);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Used mostly on logout, will remove session's cookies, delete the file,
     * and set current to null.
     * --
     * @param   mixed $session_id -- If false, then it will destroy current session,
     *                               If string, it will treat string as session id,
     *                               which needs to be destroyed.
     * --
     * @return  void
     */
    public function destroy($session_id = false)
    {
        if (!$session_id) {
            $session_id = $this->info['id'];
        }

        $this->user = null;
        $this->info = null;
        $this->cookie->remove($this->config->get('cookie_name'));
        $filename = $this->get_filename_from_id($session_id);
        if (file_exists($filename)) {
            \Core\FS::file_remove($filename);
        }
    }

    /**
     * Write session file.
     * --
     * @param  string $id
     * @param  array  $session
     * --
     * @return boolean
     */
    public function write($id, array $session)
    {
        $filename = $this->get_filename_from_id($id);
        return \Core\JSON::encode_file($filename, $session);
    }
}
