<?php

/**
 * # Log
 *
 * A basic logger.
 */
namespace mysli\toolkit; class log
{
    const debug   = 'debug';
    const info    = 'info';
    const notice  = 'notice';
    const warning = 'warning';
    const error   = 'error';
    const panic   = 'panic';

    /**
     * All messages added to the log.
     * --
     * @var array
     */
    static private $messages = [];

    /**
     * Debug information.
     * --
     * @param string $message
     * @param mixed  $context
     */
    static function debug($message, $context=null)
    {
        static::add(self::debug, $message, $context);
    }

    /**
     * General information.
     * --
     * @param string $message
     * @param mixed  $context
     */
    static function info($message, $context=null)
    {
        static::add(self::info, $message, $context);
    }

    /**
     * Significant information.
     * --
     * @param string $message
     * @param mixed  $context
     */
    static function notice($message, $context=null)
    {
        static::add(self::notice, $message, $context);
    }

    /**
     * Warning!
     * --
     * @param string $message
     * @param mixed  $context
     */
    static function warning($message, $context=null)
    {
        static::add(self::warning, $message, $context);
    }

    /**
     * Errors of any type.
     * --
     * @param string $message
     * @param mixed  $context
     */
    static function error($message, $context=null)
    {
        static::add(self::error, $message, $context);
    }

    /**
     * Fatal or any other serious full-stop situation.
     * --
     * @param string $message
     * @param mixed  $context
     */
    static function panic($message, $context=null)
    {
        static::add(self::panic, $message, $context);
    }

    /**
     * Add log message of a costume type.
     * --
     * @param string $type
     * @param string $message
     * @param mixed  $context
     *        string to use context as sender (just use __CLASS__),
     *        array  to send other arguments, 0 (if set) will be used as sender.
     */
    static function add($type, $message, $context=null)
    {
        $sender = null;

        if (is_array($context))
        {
            if (isset($context[0]))
            {
                $sender = $context[0];
                unset($context[0]);
            }

            foreach ($context as $key => $value)
            {
                if (is_a($value, 'Exception'))
                {
                    $message .= "\n\nStack trace:".$value->getTraceAsString();
                    $value = $value->getMessage();
                }

                $message = str_replace('{'.$key.'}', $value, $message);
            }
        }
        else
        {
            $sender = $context;
        }

        static::$messages[] = [
            'type'      => $type,
            'from'      => $sender,
            'timestamp' => date('c'),
            'message'   => $message
        ];
    }

    /**
     * Get all log messages, or all of particular type.
     * This is limited to messages added in this session.
     * --
     * @param  string $type
     * --
     * @return array
     */
    static function get($type=null)
    {
        if (!$type)
        {
            return static::$messages;
        }
        else
        {
            $messages = [];

            foreach (static::$messages as $message)
            {
                if ($message['type'] === $type)
                    $messages[] = $message;
            }

            return $messages;
        }
    }
}
