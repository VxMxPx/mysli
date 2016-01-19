<?php

namespace mysli\toolkit; class datetime
{
    const __use = '.{ log }';

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
        {
            $timezone = static::get_default_timezone();
        }

        // Grab current UTC datetime if not provided.
        if (!$datetime)
        {
            $datetime = gmdate('c');
        }

        $this->datetime = new \DateTime($datetime, new \DateTimeZone($timezone));
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
        return date($format, ($timestamp ? $timestamp : static::now()));
    }
}
