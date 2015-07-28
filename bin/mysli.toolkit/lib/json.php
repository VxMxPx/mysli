<?php

/**
 * # Json
 *
 * A very simple JSON manipulation class, depends on native functions.
 *
 * ## Usage
 *
 * Use `encode` to encode a JSON string, and `decode` to decode it.
 * Additional to that, methods which will read/write directly from file
 * are available:
 *
 *      json::decode_file($filename, true);
 */
namespace mysli\toolkit; class json
{
    const __use = '
        .{
            fs,
            fs.file  -> file,
            type.arr -> arr,
            exception.json
        }
    ';

    private static $json_errors = [
        JSON_ERROR_DEPTH            => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH   => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR        => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX           => 'Syntax error',
        JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        JSON_ERROR_RECURSION        => 'One or more recursive references in the value to be encoded',
        JSON_ERROR_INF_OR_NAN       => 'One or more NAN or INF values in the value to be encoded',
        JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
    ];

    /**
     * Decode a JSON file, and return it as Array or Object.
     * --
     * @param  string  $filename the file with JSON string
     * @param  boolean $assoc    when TRUE, returned object will be
     *                           converted into associative array.
     * @param  integet $depth user specified recursion depth.
     * --
     * @throws mysli\toolkit\exception\json 10 File not found.
     * --
     * @return mixed
     */
    static function decode_file($filename, $assoc=false, $depth=512)
    {
        $filename = fs::ds($filename);

        if (file::exists($filename))
        {
            $content = file::read($filename);
            return static::decode($content, $assoc, $depth);
        }
        else
        {
            throw new exception\json(
                "File not found: `{$filename}`.", 10
            );
        }
    }

    /**
     * Decode a JSON string, and return it as Array or Object.
     * --
     * @param  string  $json the json string being decoded.
     * @param  boolean $assoc when TRUE, returned object will be converted
     *                        into associative array.
     * @param  integet $depth user specified recursion depth.
     * --
     * @return mixed
     */
    static function decode($json, $assoc=false, $depth=512)
    {
        $decoded = json_decode($json, $assoc, $depth);
        static::exception_on_error();
        return $decoded;
    }

    /**
     * Save the JSON representation of a value, to the file.
     * If file exists, it will be overwritten.
     * --
     * @param string $filename
     *        The file to which the data will be saved.
     *
     * @param mixed $values
     *        The value being encoded. Can be any type except a resource.
     *        This function only works with UTF-8 encoded data.
     *
     * @param integer $options
     *        Bitmask consisting of:
     *        JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
     *        JSON_FORCE_OBJECT.
     *
     * @param integer $depth
     *        The maximum depth. Must be greater than zero.
     * --
     * @return boolean
     */
    static function encode_file($filename, $values, $options=0, $depth=512)
    {
        return (
            file::write($filename, static::encode($values, $options, $depth)) !== false
        );
    }

    /**
     * Returns the JSON representation of a value
     * --
     * @param mixed $values
     *        The value being encoded. Can be any type except a resource.
     *        This function only works with UTF-8 encoded data.
     *
     * @param integer $options
     *        Bitmask consisting of:
     *        JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
     *        JSON_FORCE_OBJECT.
     *
     * @param integer $depth
     *        The maximum depth. Must be greater than zero.
     * --
     * @return string
     */
    static function encode($values, $options=0, $depth=512)
    {
        $json = json_encode($values, $options, $depth);
        static::exception_on_error();
        return $json;
    }

    /**
     * Throw data exception if json error is detected.
     * --
     * @throws mysli\toolkit\exception\json 10 JSON error.
     */
    private static function exception_on_error()
    {
        $last_error = json_last_error();

        if ($last_error != JSON_ERROR_NONE)
        {
            if (function_exists('\\json_last_error'))
            {
                $error = \json_last_error();
            }
            else
            {
                $error = arr::get(
                    static::$json_errors,
                    json_last_error(),
                    'Unknown: ' . $json_last_error
                );
            }

            throw new exception\json("JSON error: `{$error}`.", 10);
        }
    }
}
