<?php

namespace Mysli;

class Session
{
    /**
     * Full path to the sessions file.
     * @var [type]string
     */
    protected static $filename;

    /**
     * All sessions.
     * @var array
     */
    protected static $sessions   = [];

    protected static $current_id = false;

    public static function init($filename)
    {
        if (!file_exists($filename)) {
            trigger_error("File not found: `{$filename}`", E_USER_ERROR);
        }

        self::$filename = $filename;
        self::reload();
    }

    /**
     * Will process (clean) user's agent.
     * --
     * @param   string  $agent
     * --
     * @return  string
     */
    protected static function _clean_agent($agent)
    {
        return Lib\Str::clean(
                    str_replace(' ', '_', $agent),
                    'aA1',
                    '_');
    }

    /**
     * Clear all sessions.
     * --
     * @return void
     */
    public static function clear_all()
    {
        self::destroy();
        self::$sessions = [];
        return self::write();
    }

    public static function reload()
    {
        self::$sessions = json_decode(file_get_contents(self::$filename), true);
    }

    /**
     * Will clear all expired sessions, and return the amount of removed items.
     * --
     * @return  integer
     */
    public static function cleanup()
    {
        $removed = 0;

        foreach (self::$sessions as $id => $session)
        {
            if ($session['expires_on'] < time()) {
                unset(self::$sessions[$id]);
                $removed++;
            }
        }

        if ($removed > 0) {
            self::write();
        }

        return $removed;
    }

    /**
     * Will seek for user's session!
     * If one is found, the user will be auto-logged in, and true for this function
     * will be returned, else false will be returned.
     * --
     * @return  boolean
     */
    public static function discover()
    {
        # Check if we can find session id in cookies.
        if ($session_id = Lib\Cookie::read(Lib\Cfg::get('session/cookie_name')))
        {
            # Okey we have something, check it...
            if (isset(self::$sessions[$session_id]))
            {
                $session_details = self::$sessions[$session_id];
                $user_id  = $session_details['user_id'];
                $expires  = $session_details['expires_on'];
                $ip       = $session_details['ip'];
                $agent    = $session_details['agent'];

                # For sure this user must exists and must be valid!
                if (!($user = Users::get_by_email($user_id))) { return false; }

                # Check if it is expired?
                if ($expires < time()) {
                    Lib\Log::inf("Session was found, but it's expired.");
                    self::destroy($session_id);
                    return false;
                }

                # Do we have to match IP address?
                if (Lib\Cfg::get('session/require_ip')) {
                    if ($ip !== $_SERVER['REMOTE_ADDR']) {
                        Lib\Log::inf("The IP from session file: `{$ip}`, doesn't match with actual IP: `{$_SERVER['REMOTE_ADDR']}`.");
                        self::destroy($session_id);
                        return false;
                    }
                }

                # Do we have to match agent?
                if (Lib\Cfg::get('session/require_agent')) {
                    $current_agent = self::_clean_agent($_SERVER['HTTP_USER_AGENT']);

                    if ($agent !== $current_agent) {
                        Lib\Log::inf("The agent from session file: `{$agent}`, doesn't match with actual agent: `{$current_agent}`.");
                        self::destroy($session_id);
                        return false;
                    }
                }

                # Remove old session in any case
                self::destroy($session_id);

                # Setup new user!
                Lib\Log::inf("Session was found for `{$user_id}`, user will be set!");
                self::create($user);
                return $user;
            }
            else {
                Lib\Log::inf("Session found in cookies but not in database: `{$session_id}`.");
            }
        }
        else {
            Lib\Log::inf("No session found!");
            return false;
        }
    }

    /**
     * Set session (set cookie, add info to sessions file)
     * --
     * @param   string  $user_id
     * @param   boolean $expires Null for default or costume expiration in seconds,
     *                           0, to expires when browser is closed.
     * --
     * @return  boolean
     */
    public static function create($user, $expires=null)
    {
        # Do we have valid user?
        if (is_object($user)) {
            $user_id = $user->email;
        } else {
            if (!Users::get_by_email($user)) { return false; }
            $user_id = $user;
        }

        # Set expires to some time in future. It 0 was set in config, then we
        # set it to expires imidietly when browser window is closed.
        if ($expires === null) {
            $expires = (int) Lib\Cfg::get('session/expires');
            $expires = $expires > 0 ? $expires + time() : 0;
        }
        else {
            $expires = (int) $expires;
        }

        # Create unique id
        $q_id  = time() . '_' . Lib\Str::random(20, 'aA1');

        # Store cookie
        Lib\Cookie::create(Lib\Cfg::get('session/cookie_name'), $q_id, $expires);

        # Set session file
        self::$sessions[$q_id] = array(
            'id'         => $q_id,
            'user_id'    => $user_id,
            'expires_on' => $expires === 0 ? time() + 60 * 60 : $expires,
            'ip'         => $_SERVER['REMOTE_ADDR'],
            'agent'      => self::_clean_agent($_SERVER['HTTP_USER_AGENT']),
        );

        self::$current_id = $q_id;
        return self::write();
    }

    /**
     * Used mostly on logout, will remove session's cookies and unset it in file.
     * --
     * @param   string  $session_id
     * --
     * @return  boolean
     */
    protected static function destroy($session_id)
    {
        # Remove cookies
        Lib\Cookie::remove(Lib\Cfg::get('session/cookie_name'));

        # Okay, deal with session file now...
        if (isset(self::$sessions[$session_id])) {
            unset(self::$sessions[$session_id]);

            self::cleanup();

            return self::write();
        }
        else {
            Lib\Log::war("Session wasn't set, can't unset it: `{$session_id}`.");
            return true;
        }
    }

    public static function write()
    {
        return file_put_contents(self::$filename, json_encode(self::$sessions));
    }
}