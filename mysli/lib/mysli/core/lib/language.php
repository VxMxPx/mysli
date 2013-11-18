<?php

namespace Mysli\Core\Lib;

class Language
{
    // All translations
    private static $dictionary = [];

    // List of loaded files (so that we don't load and parse a file twice)
    private static $loaded     = [];

    /**
     * Will return language debug (info)
     * --
     * @return array
     */
    public static function dump()
    {
        return [self::$loaded, self::$dictionary];
    }

    /**
     * Return language expressions in json format
     * --
     * @return string
     */
    public static function as_json()
    {
        return JSON::encode(self::$dictionary);
    }

    /**
     * Return language expressions as an array
     * --
     * @return array
     */
    public static function as_array()
    {
        return self::$dictionary;
    }

    /**
     * Will load particular language file
     * --
     * @param  string  $filename Full absolute file path.
     * --
     * @return boolean
     */
    public static function append($filename)
    {
        // Check if file was already loaded
        if (in_array($filename, self::$loaded)) {
            Log::info("File is already loaded, won't load it twice: `{$filename}`.", __FILE__, __LINE__);
            return false;
        }
        else {
            self::$loaded[] = $filename;
        }

        $processed = self::process($filename);

        if (is_array($processed)) {
            self::$dictionary = array_merge(self::$dictionary, $processed);
            Log::info("Language loaded: `{$file}`", __FILE__, __LINE__);
            return true;
        }
    }

    /**
     * Will process particular file and return an array (of expressions)
     * --
     * @param   string  $filename
     * @return  array
     */
    private static function process($filename)
    {
        $file_contents = FS::file_read($filename);
        $file_contents = Str::standardize_line_endings($file_contents);

        // Remove comments
        $file_contents = preg_replace('/^#.*$/m', '', $file_contents);

        // Add end of file notation
        $file_contents = $file_contents . "\n__#EOF#__";

        $contents = '';

        preg_match_all(
            '/^!([A-Z0-9_]+):(.*?)(?=^![A-Z0-9_]+:|^#|^__#EOF#__$)/sm',
            $file_contents,
            $contents,
            PREG_SET_ORDER);

        $result = [];

        foreach($contents as $options) {
            if (isset($options[1]) && isset($options[2])) {
                $result[trim($options[1])] = trim($options[2]);
            }
        }

        return $result;
    }

    /**
     * Will translate particular string
     * --
     * @param   string  $key
     * @param   array   $params
     * --
     * @return  string
     */
    public static function translate($key, $params=[])
    {
        if (isset(self::$dictionary[$key]))
        {
            $return = self::$dictionary[$key];

            // Check for any variables {1}, ...
            if ($params) {
                if (!is_array($params)) { $params = array($params); }

                foreach ($params as $key => $param) {
                    $key = $key + 1;
                    $return = preg_replace(
                                '/{'.$key.' ?(.*?)}/',
                                str_replace('{?}', '$1', $param),
                                $return);
                }
            }

            return $return;
        }
        else {
            Log::warn("Language key not found: `{$key}`.", __FILE__, __LINE__);
            return $key;
        }
    }
}