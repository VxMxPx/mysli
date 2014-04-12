<?php

namespace Mysli\Pkgm;

class Generator
{
    private static $meta_template =
'{
    "package" : "{{vendor}}/{{package}}",
    {{role}}
    "version" : {{version}},

    "depends_on" : {
        {{depends_on}}
    },

    "factory" : {
        "{{package}}" : "construct()"
    },

    "about" : {
        "license"     : "GPLv3",
        "upstream"    : "http://github.com/{{vendor}}/{{package}}",
        "maintainer"  : "Marko Gajšt <marko@gaj.st>",
        "description" : "This is {{package}} by {{vendor}}"
    }
}';


    private static $packages  = [
        'mysliio' => [
            'core' => [
                'require' => ['@pkgm' => 1],
                'role'    => '@core'
            ],
            'pkgm' => [
                'classes' => [
                    'exceptions/dependency.php' => [0, 'DependencyException'],
                    'script/pkgm.php'           => ['Script', 'Pkgm'],
                    'factory.php'               => [0, 'Factory']
                ],
                'require' => ['@core' => 1],
                'role'    => '@pkgm'
            ],
            'config' => [
                'require' => ['@core' => 1]
            ],
            'event' => [
                'require' => ['@core' => 1],
                'role'    => '@event'
            ]
        ],
        'avrelia' => [
            'dash' => [
                'require' => [
                    '@core' => 1,
                    'mysli/config' => 1,
                    '@event' => 1,
                    'avrelia/web' => 1,
                    'avrelia/users' => 1,
                    'avrelia/session' => 1
                ]
            ],
            'web' => [
                'require' => ['@core' => 1, '@event' => 1, 'mysli/config' => 1]
            ],
            'users' => [
                'require' => ['@core' => 1, 'avrelia/config' => 1]
            ],
            'session' => [
                'require' => ['@core' => 1, 'mysli/config' => 1, 'avrelia/users' => 1]
            ]
        ]
    ];


    public static function drop_packages()
    {
        \Core\FS::dir_remove(pkgpath());
        \Core\FS::dir_create(pkgpath());
    }

    public static function generate_packages()
    {
        foreach (self::$packages as $vendor => $packages) {
            \Core\FS::dir_create(pkgpath($vendor));
            foreach ($packages as $package => $meta) {
                \Core\FS::dir_create(pkgpath($vendor, $package));

                $role = isset($meta['role']) ? '"role" : "' . $role . '",' : '';
                $class     = Util::to_class(ds($vendor, $package), Util::FILE);
                $namespace = ltrim( Util::to_class(ds($vendor, $package), Util::BASE), '\\' );
                $depends_on = [];

                foreach ($meta['require'] as $reqpkg => $reqver) {
                    $depends_on[] = '"' . $reqpkg . '" : ">= ' . $reqver . '"';
                }

                // Check for sub-classes
                if (isset($meta['classes'])) {
                    foreach ($meta['classes'] as $file => $instr) {
                        \Core\FS::file_create_with_dir($file);
                        \Core\FS::file_replace(
                            $file,
                            self::mk_class($instr[1], $namespace . ($instr[0] ? $instr[0] : ''))
                        );
                    }
                }

                $meta_final = str_replace(
                    ['{{vendor}}', '{{package}}', '{{role}}', '{{version}}', '{{depends_on}}'],
                    [$vendor, $package, $role, 1, implode(', ', $depends_on)],
                    self::$meta_template
                );

                file_put_contents(pkgpath($vendor, $package, 'meta.json'), $meta_final);
                file_put_contents(pkgpath($vendor, $package, $package . '.php'), self::mk_class($class, $namespace));
                file_put_contents(pkgpath($vendor, $package, 'setup.php'), self::mk_class('Setup', $namespace));
            }
        }
    }

    private static function mk_class($class, $namespace)
    {
        return "<?php\nnamespace {$namespace};\nclass {$class} { public function __construct() {} }";
    }
}
