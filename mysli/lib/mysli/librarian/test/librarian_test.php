<?php

namespace Mysli;

include(__DIR__.'/../librarian.php');    // Include self
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy/private'),
    realpath(__DIR__.'/dummy/libraries')
);

class LibrarianTest extends \PHPUnit_Framework_TestCase
{
    protected $librarian;

    public function __construct()
    {
        $this->librarian = new Librarian();
    }

    public function test_get_enabled()
    {
        $libraries = $this->librarian->get_enabled();
        $this->assertEquals(
            'mysli/core',
            $libraries[0]
        );
    }

    public function test_get_enabled_detailed()
    {
        $this->assertEquals(
            json_decode(file_get_contents(datpath('/librarian/registry.json')), true),
            $this->librarian->get_enabled(true)
        );
    }

    public function test_get_disabled()
    {
        $disabled = $this->librarian->get_disabled();
        $this->assertTrue(is_array($disabled));
        $this->assertTrue(!empty($disabled));
        $this->assertTrue(in_array('mysli/manage_settings', $disabled));
    }

    public function test_get_disabled_detailed()
    {
        $disabled = $this->librarian->get_disabled(true);

        $this->assertEquals(
            'mysli/manage_settings',
            $disabled['mysli/manage_settings']['library']
        );
    }

    public function test_get_details_enabled()
    {
        $this->assertEquals(
            'mysli/core',
            $this->librarian->get_details('mysli/core')['library']
        );
    }

    public function test_get_details_disabled()
    {
        $this->assertEquals(
            'mysli/manage_settings',
            $this->librarian->get_details('mysli/manage_settings')['library']
        );
    }

    /**
     * @expectedException Mysli\Core\NotFoundException
     */
    public function test_get_details_nonexisting()
    {
        $this->assertEquals(
            [],
            $this->librarian->get_details('vendor/library')
        );
    }

    public function test_resolve_normal()
    {
        $this->assertEquals(
            'mysli/core',
            $this->librarian->resolve('mysli/core')
        );
    }

    public function test_resolve_disabled_normal()
    {
        $this->assertEquals(
            'mysli/manage_settings',
            $this->librarian->resolve('mysli/manage_settings')
        );
    }

    public function test_resolve_regex()
    {
        $this->assertEquals(
            'mysli/core',
            $this->librarian->resolve('*/core')
        );
    }

    public function test_resolve_name_failed()
    {
        $this->assertFalse(
            $this->librarian->resolve('vendor/library')
        );
    }

    // public function test_resolve_all()
    // {
    //     $this->assertEquals(
    //         [
    //             'mysli/users',
    //             'avrelia/users'
    //         ],
    //         $this->librarian->resolve_all('*/users')
    //     );
    // }

    // public function test_resolve_all_one_match()
    // {
    //     $this->assertEquals(
    //         [
    //             'avrelia/backend'
    //         ],
    //         $this->librarian->resolve_all('avrelia/backend')
    //     );
    // }

    // public function test_resolve_all_no_match()
    // {
    //     $this->assertEquals(
    //         [],
    //         $this->librarian->resolve_all('*/non_existant')
    //     );
    // }

    public function test_factory()
    {
        $object = $this->librarian->factory('mysli/dot');
        $this->assertInstanceOf('Mysli\\Dot', $object);
    }

    public function test_dependencies_factory()
    {
        $dependencies = $this->librarian->dependencies_factory('mysli/backend');
        $this->assertInstanceOf('Mysli\\Mjs', $dependencies['mjs']);
        $this->assertInstanceOf('Mysli\\Session', $dependencies['session']);
    }

    public function test_construct_setup()
    {
        $setup = $this->librarian->construct_setup('mysli/mjs');
        $this->assertInstanceOf('Mysli\\Mjs\\Setup', $setup);
    }

    public function test_construct_setup_no_file()
    {
        $setup = $this->librarian->construct_setup('avrelia/dummy');
        $this->assertFalse($setup);
    }

    public function test_autoloader()
    {
        $users = new \Mysli\Users();
        $this->assertInstanceOf('Mysli\\Users', $users);
    }

    public function test_autoloader_subclass()
    {
        $user = new \Mysli\Users\User();
        $this->assertInstanceOf('Mysli\\Users\\User', $user);
    }

    public function test_autoloader_subclass_exception()
    {
        $exception = new \Mysli\Users\BaseException();
        $this->assertInstanceOf('Mysli\\Users\\BaseException', $exception);
    }

    public function test_load_existing()
    {
        $this->assertTrue($this->librarian->load('mysli/users'));
    }

    public function test_load_non_existing()
    {
        $this->assertFalse($this->librarian->load('avrelia/non_existant'));
    }

    public function test_call()
    {
        $this->assertEquals(
            'hi',
            $this->librarian->call('mysli/backend', 'say_hi')
        );
    }

    public function test_call_params()
    {
        $random_numer = rand(0, 100);
        $this->assertEquals(
            'The random number is: ' . $random_numer,
            $this->librarian->call('mysli/backend', 'say_number', [$random_numer])
        );
    }

    public function test_is_enabled()
    {
        $this->assertTrue(
            $this->librarian->is_enabled('mysli/backend')
        );
        $this->assertFalse(
            $this->librarian->is_enabled('avrelia/writter')
        );
        $this->assertFalse(
            $this->librarian->is_enabled('avrelia/non_existant')
        );
    }

    public function test_get_dependencies_simple()
    {
        $this->assertEquals(
            [
                'enabled' => [
                    'mysli/mjs'     => '>= 0.1',
                    'mysli/core'    => '>= 0.1',
                    'mysli/session' => '>= 0.1'
                ],
                'disabled' => [],
                'missing'  => []
            ],
            $this->librarian->get_dependencies('mysli/backend')
        );
    }

    public function test_get_dependencies_disabled()
    {
        $this->assertEquals(
            [
                'enabled'  => ['mysli/core'     => '>= 0.1'],
                'disabled' => ['avrelia/reader' => '>= 0.1'],
                'missing'  => []
            ],
            $this->librarian->get_dependencies('avrelia/backend')
        );
    }

    public function test_get_dependencies_missing()
    {
        $this->assertEquals(
            [
                'enabled'  => ['mysli/core' => '>= 0.1'],
                'disabled' => [],
                'missing'  => [
                    '*/mailer'    => '>= 0.0',
                    'avrelia/sql' => '>= 1.0'
                ]
            ],
            $this->librarian->get_dependencies('avrelia/dummy')
        );
    }

    public function test_get_dependencies_deep()
    {
        $this->assertEquals(
            [
                'enabled' => [
                    'mysli/core'    => '>= 0.1',
                    'mysli/session' => '>= 0.1'
                ],
                'disabled' => [
                    'avrelia/writter' => '>= 0.1',
                    'avrelia/users'   => '>= 0.1',
                    'avrelia/reader'  => '>= 0.1'
                ],
                'missing'  => []
            ],
            $this->librarian->get_dependencies('avrelia/backend', true)
        );
    }

    public function test_get_dependees()
    {
        $this->assertEquals(
            ['mysli/backend'],
            $this->librarian->get_dependees('mysli/session')
        );
    }

    public function test_get_dependees_deep()
    {
        $this->assertEquals(
            ['mysli/backend', 'mysli/session'],
            $this->librarian->get_dependees('mysli/users', true)
        );
    }
}
