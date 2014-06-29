<?php

namespace Mysli\Pkgm;

class Generator
{
    private static $meta_template =
'{
    "name" : "{{vendor}}/{{package}}",
    "require" : {
        {{depends_on}}
    },
    "description" : "This is {{package}} by {{vendor}}",
    "license"     : "GPL-3.0"
}';


    private static $packages  = [
        'mysliio' => [
            'core' => [
                'require' => []
            ],
            'pkgm' => [
                'classes' => [
                    'exceptions/dependency.php' => [0, 'DependencyException'],
                    'script/pkgm.php'           => ['\\Script', 'Pkgm'],
                    'factory.php'               => [0, 'Factory']
                ],
                'methods' => 'public function __construct(\\Mysliio\\Event\\Event $event) {}',
                'require' => ['mysliio/core' => 1, 'mysliio/event' => 1]
            ],
            'config' => [
                'require'  => ['mysliio/core' => 1],
            ],
            'event' => [
                'require' => ['mysliio/core' => 1]
            ]
        ],
        'avrelia' => [
            'dash' => [
                'require' => [
                    'avrelia/web'     => 1,
                    'avrelia/session' => 1,
                    'avrelia/users'   => 1,
                    'mysliio/event'   => 1,
                    'mysliio/config'  => 1,
                    'mysliio/core'    => 1,
                ],
                'methods' => 'public function __construct(\\Avrelia\\Web\\Web $web, \\Avrelia\\Session\\Session $session, \\Avrelia\\Users\\Users $users, \\Mysliio\\Event\\Event $event, \\Mysliio\\Config\\Config $config) {}',
            ],
            'web' => [
                'require' => ['mysliio/core' => 1, 'mysliio/event' => 1, 'mysliio/config' => 1]
            ],
            'users' => [
                'require' => ['mysliio/event' => 1, 'mysliio/config' => 1],
                'methods' => 'public function say_hi($name) { return "Hi, {$name}"; }
                              public static function say_hello($name, $number) { return "Hello {$name}! Your number is: {$number}."; }'
            ],
            'session' => [
                'require' => ['mysliio/core' => 1, 'mysliio/config' => 1, 'avrelia/users' => 1]
            ],
            'bad' => [
                'require' => ['mysliio/core' => 1, 'avrelia/non_existant' => 1]
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

                $role       = isset($meta['role']) ? '"role" : "' . $meta['role'] . '",' : '';
                $class      = Util::to_class(ds($vendor, $package), Util::FILE);
                $namespace  = ltrim( Util::to_class(ds($vendor, $package), Util::BASE), '\\' );
                $depends_on = [];

                foreach ($meta['require'] as $reqpkg => $reqver) {
                    $depends_on[] = '"' . $reqpkg . '" : ">= ' . $reqver . '"';
                }

                // Check for sub-classes
                if (isset($meta['classes'])) {
                    foreach ($meta['classes'] as $file => $instr) {
                        $file = pkgpath($vendor, $package, $file);
                        \Core\FS::file_create_with_dir($file);
                        \Core\FS::file_replace(
                            $file,
                            self::mk_class($instr[1], $namespace . ($instr[0] ? $instr[0] : ''))
                        );
                    }
                }

                // Create factory entries
                // if (isset($meta['factory'])) {
                //     $factory = [];
                //     foreach ($meta['factory'] as $fac_package => $instructions) {
                //         $factory[] = '"' . $fac_package . '" : "' . $instructions . '"';
                //     }
                //     $factory = implode(',', $factory);
                // } else {
                //     $factory = '"'.$package.'" : "construct()"';
                //     if (isset($meta['classes'])) {
                //         foreach ($meta['classes'] as $class => $class_info) {
                //             $factory = '"'.substr($class, 0, -4).'" : "construct()"';
                //         }
                //     }
                // }

                $meta_final = str_replace(
                    ['{{vendor}}', '{{package}}', '{{depends_on}}'],
                    [$vendor, $package, implode(', ', $depends_on)],
                    self::$meta_template
                );

                file_put_contents(pkgpath($vendor, $package, 'mysli.pkg.json'), $meta_final);
                file_put_contents(
                    pkgpath($vendor, $package, $package . '.php'),
                    self::mk_class(
                        $class,
                        $namespace,
                        (isset($meta['methods']) ? $meta['methods'] : '')
                    )
                );
                file_put_contents(pkgpath($vendor, $package, 'setup.php'), self::mk_class('Setup', $namespace));
            }
        }
    }

    private static function mk_class($class, $namespace, $methods = '')
    {
        return "<?php\nnamespace {$namespace};\nclass {$class} { {$methods} }";
    }
}
