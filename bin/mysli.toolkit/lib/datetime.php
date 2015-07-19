<?php

/**
 * # Datetime
 *
 * Allows manipulations of date and time.
 * This is a flexible class with a couple of static shortcuts, and possibility
 * to be instantiated.
 *
 * A native \DateTime object can be used, rather than this class, which
 * is here only to simplify things a little bit. For example, there's no need to
 * construct \TimeZone object, to change timezone, etc...
 *
 * ## Usage
 *
 *      // New instance, with current UTC datetime
 *      $datetime = new datetime();
 *      $datetime->format(datetime::iso8601); // == $datetime->format('c');
 *
 * Timezone can be easily changed at any time:
 *
 *      $datetime->set_timezone('Europe/Ljubljana');
 *
 * The current date/time can be changed...
 *
 *      $datetime->set_datetime(datetime::now());
 *
 * ...or modified (will return a Unix timestamp):
 *
 *      $datetime->modify('+1 day');
 *
 * To get datetime difference, `diff` method can be called (returns \DateInterval):
 *
 *      $datetime->diff($a_valid_datetime_string);
 */
namespace mysli\toolkit; class datetime
{
    const __use = '.log';

    const timestamp = 'U';
    const time      = 'H:i:s';
    const day       = 'd';
    const month     = 'm';
    const year      = 'Y';
    const sort      = 'YmdHis';
    const atom      = 'Y-m-d\TH:i:sP';
    const cookie    = 'l, d-M-Y H:i:s T';
    const iso8601   = 'Y-m-d\TH:i:sO';
    const rfc822    = 'D, d M y H:i:s O';
    const rfc850    = 'l, d-M-y H:i:s T';
    const rfc1036   = 'D, d M y H:i:s O';
    const rfc1123   = 'D, d M Y H:i:s O';
    const rfc2822   = 'D, d M Y H:i:s O';
    const rfc3339   = 'Y-m-d\TH:i:sP';
    const rss       = 'D, d M Y H:i:s O';
    const w3c       = 'Y-m-d\TH:i:sP';

    /**
     * Datetime instance.
     * --
     * @var \DateTime
     */
    protected $datetime;

    /**
     * Instance of a datetime object.
     * --
     * @param string $datetime
     *        A valid date/time string.
     *        See: http://php.net/manual/en/datetime.formats.php
     *        If not provided, `now` will be used.
     *
     * @param string $timezone
     *        A string representation of timezone.
     *        See: http://php.net/manual/en/timezones.php
     *        If not provided, the PHP's default timezone will be used.
     */
    function __construct($datetime=null, $timezone=null)
    {
        // Get default timezone if not set.
        if (is_null($timezone))
            $timezone = self::get_default_timezone();

        // Grab current UTC datetime if not provided.
        if (!$datetime)
            $datetime = gmdate('c');

        $this->datetime = new \DateTime(
            $datetime,
            new \DateTimeZone($timezone)
        );
    }

    /**
     * Set new date/time.
     * --
     * @param mixed $datetime
     *        A unixtimestamp or a string representation of datetime.
     */
    function set_datetime($datetime)
    {
        if (!is_numeric($datetime))
        {
            $datetime = strtotime($datetime);
        }

        $this->datetime->setTimestamp($datetime);
    }

    /**
     * Set new timezone.
     * --
     * @param string $timezone
     */
    function set_timezone($timezone)
    {
        $this->datetime->setTimezone(new \DateTimeZone($timezone));
    }

    /**
     * Get timezone.
     * --
     * @return \DateTimeZone
     */
    function get_timezone()
    {
        return $this->datetime->getTimezone();
    }

    /**
     * Format date/time.
     * --
     * @param string $format
     * @param string $datetime
     * --
     * @return string
     */
    function format($format)
    {
        return $this->datetime->format($format);
    }

    /**
     * Parse about any English textual date/time description
     * into a Unix timestamp.
     * --
     * @param string $modify
     * --
     * @return string
     */
    function modify($modify)
    {
        return $this->datetime->modify($modify)->format(self::timestamp);
    }

    /**
     * Return difference between two DateTimeInterface objects.
     * --
     * @param string $datetime
     * @param string $timezone If not provided, current will be used.
     * --
     * @return \DateInterval
     */
    function diff($datetime, $timezone=null)
    {
        $timezone = new \DateTimeZone(
            $timezone ?: $this->get_timezone()->getName()
        );

        return date_diff(
            $this->datetime,
            new \DateTime($datetime, $timezone)
        );
    }

    /*
    --- Static -----------------------------------------------------------------
     */

    /**
     * Change the default timezone (will be used by all date/time functions).
     * --
     * @param string $timezone
     */
    static function set_default_timezone($timezone)
    {
        log::debug('Default timezone set to: `{$timezone}`.', __CLASS__);
        date_default_timezone_set($timezone);
    }

    /**
     * Get the default timezone (used by all date/time functions).
     * --
     * @return string
     */
    static function get_default_timezone()
    {
        return date_default_timezone_get();
    }

    /**
     * Return Unix timestamp for current date/time!
     * --
     * @return integer
     */
    static function now()
    {
        return time();
    }

    /**
     * Format date/time.
     * --
     * @param string $format
     * @param mixed  $timestamp
     * --
     * @return string
     */
    static function f($format, $timestamp=null)
    {
        return date($format, ($timestamp ? $timestamp : self::now()));
    }
}
