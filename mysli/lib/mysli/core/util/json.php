<?php

namespace Mysli\Core\Util;

class JSON
{
    /**
     * Decode a JSON file, and return it as Array or Object
     *
     * @param  string   $filename -- The file with JSON string
     * @param  bool     $assoc    -- When TRUE, returned object will be
     *                               converted into associative array.
     * @param  integet  $depth    -- User specified recursion depth.
     * @return mixed
     */
    public static function decode_file($filename, $assoc=false, $depth=512)
    {
        $filename = ds($filename);

        if (file_exists($filename)) {
            $content = \FS::file_read($filename);
            return self::decode($content, $assoc, $depth);
        } else {
            trigger_error("File not found: `{$filename}`.", E_USER_WARNING);
            return false;
        }
    }

    /**
     * Decode a JSON string, and return it as Array or Object
     *
     * @param string   $json     -- The json string being decoded.
     * @param bool     $assoc    -- When TRUE, returned object will be converted
     *                              into associative array.
     * @param integet  $depth    -- User specified recursion depth.
     * @return mixed
     */
    public static function decode($json, $assoc=false, $depth=512)
    {
        $decoded = json_decode($json, $assoc, $depth);

        if (json_last_error() != JSON_ERROR_NONE) {
            $JSONErrors = array(
                JSON_ERROR_NONE      => 'No error has occurred',
                JSON_ERROR_DEPTH     => 'The maximum stack depth has been exceeded',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX    => 'Syntax error',
            );
            throw new \Mysli\Core\DataException(
                "JSON decode error: `" . $JSONErrors[json_last_error()] . '`.'
            );
            return false;
        }
        else {
            return $decoded;
        }
    }

    /**
     * Save the JSON representation of a value, to the file.
     * If file exists, it will be overwritten.
     *
     * @param string $filename -- The file to which the data will be saved.
     * @param mixed  $values   -- The value being encoded. Can be any type
     *                            except a resource . This function only works
     *                            with UTF-8 encoded data.
     * @param int    $options  -- Bitmask consisting of JSON_HEX_QUOT,
     *                            JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
     *                            JSON_FORCE_OBJECT.
     * @return bool
     */
    public static function encode_file($filename, $values, $options=0)
    {
        return \FS::file_replace(
                    self::encode($values, $options),
                    $filename,
                    false
                );
    }

    /**
     * Returns the JSON representation of a value
     *
     * @param mixed  $values   -- The value being encoded. Can be any type
     *                            except a resource . This function only works
     *                            with UTF-8 encoded data.
     * @param int    $options  -- Bitmask consisting of JSON_HEX_QUOT,
     *                            JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
     *                            JSON_FORCE_OBJECT.
     * @return string
     */
    public static function encode($values, $options=0)
    {
        return json_encode($values, $options);
    }
}
