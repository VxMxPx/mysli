<?php

namespace mysli\framework\json;

__use(__namespace__, '
    mysli/framework/fs/{fs,file}
    mysli/framework/type/arr
    mysli/framework/exception/{...} AS framework/exception/{...}
');

class json {

    private static $json_errors = [
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR =>
            'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 =>
            'Malformed UTF-8 characters, possibly incorrectly encoded',
        JSON_ERROR_RECURSION =>
            'One or more recursive references in the value to be encoded',
        JSON_ERROR_INF_OR_NAN =>
            'One or more NAN or INF values in the value to be encoded',
        JSON_ERROR_UNSUPPORTED_TYPE =>
            'A value of a type that cannot be encoded was given',
    ];

    /**
     * Decode a JSON file, and return it as Array or Object
     * @param  string  $filename the file with JSON string
     * @param  boolean $assoc    when TRUE, returned object will be
     * converted into associative array.
     * @param  integet $depth user specified recursion depth.
     * @return mixed
     */
    static function decode_file($filename, $assoc=false, $depth=512) {
        $filename = fs::ds($filename);
        if (file::exists($filename)) {
            $content = file::read($filename);
            return self::decode($content, $assoc, $depth);
        } else {
            throw new framework\exception\not_found(
                "File not found: `{$filename}`.", 1);
        }
    }
    /**
     * Decode a JSON string, and return it as Array or Object
     * @param  string  $json the json string being decoded.
     * @param  boolean $assoc when TRUE, returned object will be converted
     * into associative array.
     * @param  integet $depth user specified recursion depth.
     * @return mixed
     */
    static function decode($json, $assoc=false, $depth=512) {
        $decoded = json_decode($json, $assoc, $depth);
        self::exception_on_error();
        return $decoded;
    }
    /**
     * Save the JSON representation of a value, to the file.
     * If file exists, it will be overwritten.
     * @param string $filename the file to which the data will be saved.
     * @param mixed  $values the value being encoded. Can be any type
     * except a resource. This function only works with UTF-8 encoded data.
     * @param integer $options bitmask consisting of
     * JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
     * JSON_FORCE_OBJECT.
     * @param  integer $depth the maximum depth. Must be greater than zero.
     * @return boolean
     */
    static function encode_file($filename, $values, $options=0,
                                $depth=512) {
        return (file::write(
                $filename, self::encode($values, $options, $depth)) !== false);
    }
    /**
     * Returns the JSON representation of a value
     * @param mixed  $values the value being encoded. Can be any type
     * except a resource. This function only works with UTF-8 encoded data.
     * @param integer $options bitmask consisting of
     * JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS,
     * JSON_FORCE_OBJECT.
     * @param  integer $depth the maximum depth. Must be greater than zero.
     * @return string
     */
    static function encode($values, $options=0, $depth=512) {
        $json = json_encode($values, $options, $depth);
        self::exception_on_error();
        return $json;
    }
    /**
     * Throw data exception if json error is detected.
     * @return null
     */
    private static function exception_on_error() {
        $last_error = json_last_error();
        if ($last_error != JSON_ERROR_NONE) {
            if (function_exists('\\json_last_error')) {
                $error = \json_last_error();
            } else {
                $error = arr::get(
                    self::$json_errors, json_last_error(),
                    'Unknown: ' . $json_last_error);
            }
            throw new framework\exception\data("JSON error: `{$error}`.");
        }
    }
}
