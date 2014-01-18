<?php

namespace Mysli;

class Language
{
    // All translations
    protected $dictionary = [];

    // List of loaded files (so that we don't load and parse a file twice)
    protected $loaded     = [];

    /**
     * Will return language debug (info)
     * --
     * @return array
     */
    public function dump()
    {
        return [$this->loaded, $this->dictionary];
    }

    /**
     * Return language expressions in json format
     * --
     * @return string
     */
    public function as_json()
    {
        return \JSON::encode($this->dictionary);
    }

    /**
     * Return language expressions as an array
     * --
     * @return array
     */
    public function as_array()
    {
        return $this->dictionary;
    }

    /**
     * Will load particular language file
     * --
     * @param  string  $filename Full absolute file path.
     * --
     * @return boolean
     */
    public function append($filename)
    {
        // Check if file was already loaded
        if (in_array($filename, $this->loaded)) {
            // $this->log->info(
            //     "File is already loaded, won't load it twice: '{$filename}'.",
            //     __FILE__, __LINE__
            // );
            return false;
        }
        else {
            $this->loaded[] = $filename;
        }

        $processed = $this->process($filename);

        if (is_array($processed)) {
            $this->dictionary = array_merge($this->dictionary, $processed);
            // $this->log->info("Language loaded: '{$file}'", __FILE__, __LINE__);
            return true;
        }
    }

    /**
     * Will process particular file and return an array (of expressions)
     * --
     * @param   string  $filename
     * @return  array
     */
    private function process($filename)
    {
        $file_contents = \FS::file_read($filename);
        $file_contents = \Str::to_unix_line_endings($file_contents);

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
    public function translate($key, array $params = [])
    {
        if (isset($this->dictionary[$key]))
        {
            $return = $this->dictionary[$key];

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
            trigger_error("Language key not found: '{$key}'.", E_USER_WARNING);
            return $key;
        }
    }
}
