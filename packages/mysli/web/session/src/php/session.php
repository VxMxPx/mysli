<?php

namespace mysli\web\session;

__use(__namespace__, '
    mysli.util.config
    mysli.framework.json
    mysli.framework.type/str
    mysli.framework.fs/fs,file
    mysli.framework.exception/* -> framework\exception\*
    mysli.web.users
    mysli.web.cookie
    mysli.web.request
');

class session
{
    private static $user;
    private static $info;

    /**
     * Current session information, like ID, etc...
     * @return mixed  array or null session is not set.
     */
    static function info()
    {
        return self::$info;
    }
    /**
     * Get current user.
     * @return mixed  object or null if no user is set.
     */
    static function user()
    {
        return self::$user;
    }
    /**
     * Find session, and set user if found
     * @return boolean
     */
    static function discover()
    {
        $c = config::select('mysli/web/session');

        // Check if we can find session id in cookies.
        if (!($session_id = cookie::get($c->get('cookie_name'))))
        {
            return false;
        }

        // Clean session id
        $session_id = str::clean($session_id, 'aA1', '_');

        // Does such session_id exists?
        $session_path = self::path_from_id($session_id);
        if (!file::exists($session_path))
        {
            self::destroy($session_id);
            return false;
        }

        // Read session file
        $session = json::decode_file($session_path, true);
        if (!is_array($session))
        {
            self::destroy($session_id);
            return false;
        }

        // Is it expired?
        if ((int) $session['expires_on'] < time())
        {
            self::destroy($session_id);
            return false;
        }

        // Do we need identical IP address?
        if ($c->get('require_ip'))
        {
            if ($session['ip'] !== request::ip())
            {
                self::destroy($session_id);
                return false;
            }
        }

        // Do we need identical agent?
        if ($c->get('require_agent'))
        {
            if ($session['agent'] !== md5(request::agent()))
            {
                self::destroy($session_id);
                return false;
            }
        }

        // Get the user...
        $user = users::get_by_id($session['user_id']);
        if (!$user)
        {
            self::destroy($session_id);
            return false;
        }

        if (!$user->is_active || $user->is_deleted)
        {
            self::destroy($session_id);
            return false;
        }

        self::$user = $user;
        self::$info = $session;

        self::extend($session);

        return true;
    }
    /**
     * Set a new user and create sessions
     * @param object $user
     * @param mixed $expires null for default
     *                       int costume expiration in seconds,
     *                       0 to expires when browser is closed
     * @return boolean
     */
    static function set($user, $expires=null)
    {
        if (!method_exists($user, 'id'))
        {
            throw new exception\session(
                "User object need to have `id` property.", 1
            );
        }
        self::$user = $user;

        return self::create($user->id(), $expires);
    }
    /**
     * Create session (set cookie, etc...) (assume user is already set)
     * @param  string  $uid     A user id.
     * @param  mixed   $expires null for default
     *                          int costume expiration in seconds,
     *                          0 to expires when browser is closed
     * @return boolean
     */
    static function create($uid, $expires=null)
    {
        $c = config::select('mysli/web/session');

        if ($expires === null)
        {
            $expires = (int) $c->get('expires');
            $expires = $expires > 0 ? $expires + time() : 0;
        }
        else
        {
            $expires = (int) $expires;
        }

        // Create a unique id
        $id  = time() . '_' . str::random(20, 'aA1');

        // Store cookie
        cookie::set($c->get('cookie_name'), $id, '/', $expires);

        // Set session file
        $session = [
            'id'               => $id,
            'user_id'          => $uid,
            'expires_on'       => $expires === 0 ? time() + 60 * 60 : $expires,
            'expires_relative' => $expires,
            'ip'               => request::ip(),
            'agent'            => request::agent(),
        ];

        self::$info = $session;
        return self::write($id, $session);
    }
    /**
     * Will clear all expired sessions, and return the amount of removed items.
     * @return integer number of removed sessions
     */
    static function cleanup()
    {
        $removed = 0;
        $path = fs::datpath('mysli/web/session/sessions');
        $sessions = fs::ls($path);

        foreach ($sessions as $session_file)
        {
            if (substr($session_file, -13) !== '_session.json')
            {
                continue;
            }

            $filename = ds($path, $session_file);
            $session = json::decode_file($filename);

            if ($session['expires_on'] < time())
            {
                file::remove($filename);
                $removed++;
            }
        }

        return $removed;
    }
    /**
     * Used mostly on logout, will remove session's cookies, delete the file,
     * and set current user to null.
     * @param mixed $session_id false to destroy current session,
     *                          string - session id which needs to be destroyed
     */
    static function destroy($session_id=false)
    {
        if (!$session_id)
        {
            $session_id = self::$info['id'];
        }

        self::$user = null;
        self::$info = null;

        cookie::remove(config::select('mysli.web.session', 'cookie_name'));

        $filename = self::path_from_id($session_id);

        if (file::exists($filename))
        {
            file::remove($filename);
        }
    }

    /**
     * Renew session (extend timeout, etc...)
     * @param  array   $session
     * @return boolean
     */
    private static function extend(array $session)
    {
        $c = config::select('mysli.web.session');

        // Sessions which expires when browser window is closed,
        // doesn't need to be extended
        if ($session['expires_relative'] === 0)
        {
            return true;
        }

        // If we have to change id on renew,
        // then we'll destroy current session and set new one.
        if ($c->get('change_id_on_renew'))
        {
            self::destroy($session['id']);
            return self::create($session['uid'], $session['expires_relative']);
        }

        // Set expires to some time in future. It 0 was set, then we
        // set it to expires immediately when browser window is closed.
        $expires = $session['expires_relative'];

        if ($expires === null)
        {
            $expires = (int) $c->get('expires');
            $expires = $expires > 0 ? $expires + time() : 0;
        }
        else
        {
            $expires = (int) $expires;
        }

        $session['expires_on'] = $expires;
        $session['ip']         = request::ip();
        $session['agent']      = request::agent();

        cookie::set($c->get('cookie_name'), $session['id'], '/', $expires);

        return self::write($session['id'], $session);
    }
    /**
     * Get session's absolute path from an id.
     * @param  string $id
     * @return string
     */
    private static function path_from_id($id)
    {
        return fs::datpath('mysli/web/session/sessions', $id . '_session.json');
    }
    /**
     * Write session file.
     * @param  string $id
     * @param  array  $session
     * @return boolean
     */
    private static function write($id, array $session)
    {
        return json::encode_file(self::path_from_id($id), $session);
    }
}
