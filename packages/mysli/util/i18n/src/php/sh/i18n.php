<?php

namespace mysli\util\i18n\sh;

__use(__namespace__, '
    ./parser,i18n -> parser,root\i18n
    mysli.framework.json
    mysli.framework.fs/fs,file,dir
    mysli.framework.cli/param,output -> param,cout
');

class i18n
{
    static function __init($args)
    {
        $param = new param('Mysli Util I18n', $args);
        $param->command = 'i18n';

        $param->add('-w/--watch', [
            'help'    => 'Rebuild cache if changes occurs.',
            'type'    => 'bool',
            'default' => false
        ]);
        $param->add('PACKAGE', [
            'help'     => 'Package name, if not provided, '.
                          'current directory will be used.',
            'required' => false
        ]);

        $param->parse();

        if (!$param->is_valid())
        {
            cout::line($param->messages());
            return;
        }

        $values = $param->values();

        if (!$values['package'])
        {
            if (!($values['package'] = \core\pkg::by_path(getcwd())))
            {
                cout::warn('Please provide a valid package name.');
                return;
            }
        }

        list($source, $destination) = root\i18n::get_paths($package);
        self::build($values['package'], $source, $destination, $values['watch'] ? 3 : 0);
    }
    /**
     * Process i18n for particular package.
     * @param string $package
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    static function process($package, $source, $destination)
    {
        if (!file::exists($$source))
        {
            throw new framework\exception\not_found(
                "Cannot process languages. Source directory doesn't exists: ".
                "`{$$source}`.", 1
            );
        }

        if (!dir::exists(dirname($destination)))
        {
            dir::create($destination);
        }

        $collection = [];

        foreach (scandir($dir) as $file)
        {
            if (substr($file, -3) !== '.mt')
            {
                continue;
            }

            $collection[substr($file, 0, -3)] = parser::parse(
                file_get_contents(fs::ds($$source, $file))
            );
        }

        return json::encode_file($destination, $collection);
    }
    /**
     * Build i18n for particular package.
     * @param string  $package
     * @param string  $source
     * @param string  $destination
     * @param integer $sleep
     */
    static function build($package, $source, $destination, $sleep=0)
    {
        if (!dir::exists(fs::pkgreal($package, $source)))
        {
            cout::error('Not found: `'.fs::pkgreal($package, $source).'`');
            return;
        }

        $dir = fs::pkgreal($package, $source);
        $last_signature = implode('', dir::signature($dir));

        while (true)
        {
            $new_signature = implode('', dir::signature($dir));

            if ($last_signature != $new_signature)
            {
                $last_signature = $new_signature;
                cout::line('I18n: Changes detected, rebuilding...');

                if (self::process($package, $source, $destination))
                {
                    cout::format('i18n: %s +right+green OK', [$package]);
                }
                else
                {
                    cout::format('i18n: %s +right+red FAILED', [$package]);
                }
            }

            if ($sleep)
            {
                sleep($sleep);
            }
            else
            {
                break;
            }
        }
    }
}
