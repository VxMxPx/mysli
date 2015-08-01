<?php

namespace mysli\tplp; class template
{
    const __use = '
        .{
            tplp,
            parser,
            exception.template
        }
        mysli.toolkit.{
            fs.fs   -> fs,
            fs.file -> file
        }
    ';

    /**
     * Full absolute path to the templates root, for this instance.
     * --
     * @var string
     */
    private $root;

    /**
     * Translator instance.
     * --
     * @var mixed
     */
    private $translator;

    /**
     * Variables set for this instance.
     * --
     * @var array
     */
    private $variables = [];

    /**
     * Replaced files.
     * --
     * @var array
     */
    private $replace = [];

    /**
     * Instance of template.
     * --
     * @param string $root
     */
    function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * Set translator service for template.
     * --
     * @param object $translator
     */
    function set_translator($translator)
    {
        $this->translator = $translator;

        $this->set_function(
            'translator_service',
            function ()
            {
                return call_user_func_array(
                    [$this->translator, 'translate'], func_get_args()
                );
            }
        );
    }

    /**
     * Set local function for template.
     * --
     * @param string   $name
     * @param callable $call
     */
    function set_function($name, $call)
    {
        $this->variables["tplp_func_{$name}"] = $call;
    }

    /**
     * Unset local function by name.
     * --
     * @param string $name
     */
    function unset_function($name)
    {
        if (isset($this->variables["tplp_func_{$name}"]))
            unset($this->variables["tplp_func_{$name}"]);
    }

    /**
     * Set a local variable.
     * --
     * @param string $name
     * @param mixed  $value
     * --
     * @throws mysli\tplp\exception\template
     *         10 Invalid variable name, a variable cannot start with `tplp_`.
     */
    function set_variable($name, $value)
    {
        if (substr($name, 0, 5) === 'tplp_')
        {
            throw new exception\template(
                "Invalid variable name `{$name}`, ".
                "a variable cannot start with: `tplp_`", 10
            );
        }

        $this->variables[$name] = $value;
    }

    /**
     * Unset a local variable.
     * --
     * @param string $name
     */
    function unset_variable($name)
    {
        if (isset($this->variables[$name]))
            unset($this->variables[$name]);
    }

    /**
     * Replace a local file with an external one, or set file which
     * internally doesn't exists.
     *
     * The behavior of this method is exactly the same, as if you'd copy an
     * external file to the current root. The difference is, this method will not
     * actually copy a file.
     *
     * If source file will have any includes,
     * those will be made from the current root.
     * --
     * @param string $file
     *        A file which is bring replaced (no extension).
     *
     * @param mixed $with
     *        Array:
     *            Provide an array in following format:
     *            [ string $root, string $filename ]
     *
     *            $root: A root directory from which file is being loaded.
     *            $filename: Actual filename of file being included.
     *
     *        String:
     *            Provide actual template, rather than file. This will not seek
     *            for file, but rather just use the provided template.
     */
    function replace($file, $with)
    {
        $this->replace[$file] = $with;
    }

    /**
     * Render template.
     * --
     * @param string $file
     * @param array  $variables
     * --
     * @throws mysli\tplp\exception\template 10 File not found...
     * @throws mysli\tplp\exception\template 20 Parsing failed...
     * --
     * @return string
     */
    function render($file, array $variables=[])
    {
        $parsed = $this->locate_parsed($file);

        if (!$parsed)
        {
            $source = $this->locate_source($file);

            if (!$source)
                throw new exception\template(
                    "File not found: `{$file}` for `{$this->root}`.", 10
                );

            // Parse...
            try
            {
                $contents = parser::file($source[1], $source[0], $this->replace);
            }
            catch (\Exception $e)
            {
                throw new exception\template(
                    "Parsing of file `{$file}` failed with message: ".
                    $e->getMessage(), 20
                );
            }

            // Save file to temporary folder
            $tempfilename = $this->tmppath_from_file($source[1], $source[0]);
            file::write(fs::tmppath('tplp', $tempfilename), $contents);

            $parsed = [
                fs::tmppath('tplp'),
                $tempfilename,
                fs::tmppath('tplp', $tempfilename)
            ];
        }

        $file = $parsed[2];

        // Assign variables...
        $variables = array_merge($this->variables, $variables);

        foreach($variables as $_tplpvar => $_tplpval)
        {
            $$_tplpvar = $_tplpval;
        }

        ob_start();
        if (isset($this->replace[$parsed[1]]) &&
            is_string($this->replace[$parsed[1]]))
        {
            eval($this->replace[$parsed[1]]);
        }
        else
        {
            include($file);
        }
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Check weather particular template SOURCE file exists.
     * This will omit temporary folder, ~dist
     * and replaced files (@see static::replace()).
     * --
     * @param string $file
     * --
     * @return boolean
     */
    function has($file)
    {
        if (file::exists("{$this->root}/{$file}.php"))
            return true;
        elseif (file::exists("{$this->root}/{$file}.tpl.html"))
            return true;
        else
            return false;
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Get a full absolute path to the temporary file, from a filename.
     * Result example, for a file named: `blog/post`
     * cb647a447c6841d5e5840b44194ed0a4-blog-d-post.php
     * --
     * @param string $file
     * @param string $root
     * --
     * @return string
     */
    protected function tmppath_from_file($file, $root)
    {
        return
            md5("{$root}{$file}.php").
            '-'.
            str_replace('/', '-d-', $file).'.php';
    }

    /**
     * Locate particular file, and return a full-file path.
     * This will look only for parsed (`.php`) versions of file.
     *
     * Primary query target will be replaced files (@see static::replace()).
     * --
     * @param string $file
     * @param string $root If not provided `$this->root` will be used.
     * --
     * @return array [ string $root, string $file, string $full_path ]
     */
    protected function locate_parsed($file, $root=null)
    {
        if (!$root)
            $root = $this->root;

        $checks[] = [ $root, $file ];

        // Insert replace to be checked
        if (isset($this->replace[$file]))
            array_unshift($checks, $this->replace[$file]);

        foreach ($checks as $file)
        {
            // We're replacing from source with...
            if (is_string($file))
            {
                return [
                    $this->root, "{$file}.php",  "{$this->root}/{$file}.php"
                ];
            }
            else
            {
                list($root, $file) = $file;
            }

            $temporariy = [
                fs::tmppath('tplp'),
                $this->tmppath_from_file($file, $root),
            ];
            $temporariy[] = implode('/', $temporariy);

            $dist   = [ "{$root}/~dist", "{$file}.php" ];
            $dist[] = implode("/", $dist);

            $source   = [ "{$root}", "{$file}.php" ];
            $source[] = implode('/', $source);

            if (file::exists($temporariy[2]))
                return $temporariy;
            elseif (file::exists($dist[2]))
                return $dist;
            elseif (file::exists($source[2]))
                return $source;
        }

        return null;
    }

    /**
     * Locate particular file, and return a full-file path.
     * This will look only for source (`.tpl.html`) versions of file.
     * --
     * @param string $file
     * --
     * @return array [ string $root, string $file, string $full_path ]
     */
    protected function locate_source($file, $root=null)
    {
        if (!$root)
            $root = $this->root;

        $checks[] = [ $root, $file ];

        // Insert replace to be checked
        if (isset($this->replace[$file]))
            array_unshift($checks, $this->replace[$file]);

        foreach ($checks as $file)
        {
            // We're replacing from source with...
            if (is_string($file))
            {
                return [
                    $this->root,
                    "{$file}.tpl.html",
                    "{$this->root}/{$file}.tpl.html"
                ];
            }
            else
            {
                list($root, $file) = $file;
            }

            $source = "{$root}/{$file}.tpl.html";

            if (file::exists($source))
                return [ $root, "{$file}.tpl.html", $source ];
        }

        return null;
    }
}
