<?php

namespace Mysli\Core\Lib;

include(__DIR__.'/../../core.php');
\Mysli\Core::init(
    __DIR__.'/public_dummy',
    __DIR__.'/libraries_dummy',
    __DIR__.'/data_dummy'
);

class LibrarianTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_enabled()
    {
        $libraries = Librarian::get_enabled();
        $this->assertEquals(
            'mysli/core',
            $libraries[0]
        );
    }

    public function test_get_enabled_detailed()
    {
        $this->assertEquals(
            json_decode(file_get_contents(__DIR__.'/data_dummy/core/libraries.json'), true),
            Librarian::get_enabled(true)
        );
    }

    public function test_get_disabled()
    {
        $disabled = Librarian::get_disabled();
        $this->assertTrue(is_array($disabled));
        $this->assertTrue(!empty($disabled));
        $this->assertTrue(in_array('mysli/manage_settings', $disabled));
    }

    public function test_get_disabled_detailed()
    {
        $disabled = Librarian::get_disabled(true);

        $this->assertEquals(
            'mysli/manage_settings',
            $disabled['mysli/manage_settings']['library']
        );
    }

    public function test_get_details_enabled()
    {
        $this->assertEquals(
            'mysli/core',
            Librarian::get_details('mysli/core')['library']
        );
    }

    public function test_get_details_disabled()
    {
        $this->assertEquals(
            'mysli/manage_settings',
            Librarian::get_details('mysli/manage_settings')['library']
        );
    }

    /**
     * @expectedException Mysli\Core\FileSystemException
     */
    public function test_get_details_nonexisting()
    {
        $this->assertEquals(
            [],
            Librarian::get_details('vendor/library')
        );
    }

    public function test_resolve_normal()
    {
        $this->assertEquals(
            'mysli/core',
            Librarian::resolve('mysli/core')
        );
    }

    public function test_resolve_regex()
    {
        $this->assertEquals(
            'mysli/core',
            Librarian::resolve('*/core')
        );
    }

    public function test_resolve_name_failed()
    {
        $this->assertFalse(
            Librarian::resolve('vendor/library')
        );
    }

    public function test_resolve_all()
    {
        $this->assertEquals(
            [
                'mysli/users',
                'avrelia/users'
            ],
            Librarian::resolve_all('*/users')
        );
    }

    public function test_resolve_all_one_match()
    {
        $this->assertEquals(
            [
                'avrelia/backend'
            ],
            Librarian::resolve_all('avrelia/backend')
        );
    }

    public function test_resolve_all_no_match()
    {
        $this->assertEquals(
            [],
            Librarian::resolve_all('*/non_existant')
        );
    }

    public function test_factory()
    {
        $object = Librarian::factory('mysli/dot');
        $this->assertInstanceOf('Mysli\\Dot', $object);
    }

    public function test_dependencies_factory()
    {
        $dependencies = Librarian::dependencies_factory('mysli/backend');
        $this->assertFalse($dependencies['core']);
        $this->assertInstanceOf('Mysli\\Mjs', $dependencies['mjs']);
        $this->assertInstanceOf('Mysli\\Session', $dependencies['session']);
    }

    public function test_construct_setup()
    {
        $setup = Librarian::construct_setup('mysli/mjs');
        $this->assertInstanceOf('Mysli\\Mjs\\Setup', $setup);
    }

    public function test_construct_setup_no_file()
    {
        $setup = Librarian::construct_setup('avrelia/dummy');
        $this->assertFalse($setup);
    }

    public function test_autoloader()
    {
        $users = new \Mysli\Users();
        $this->assertInstanceOf('Mysli\\Users', $users);
    }

    public function test_load_existing()
    {
        $this->assertTrue(Librarian::load('mysli/users'));
    }

    public function test_load_non_existing()
    {
        $this->assertFalse(Librarian::load('avrelia/non_existant'));
    }

    public function test_call()
    {
        $this->assertEquals(
            'hi',
            Librarian::call('mysli/backend', 'say_hi')
        );
    }

    public function test_call_params()
    {
        $random_numer = rand(0, 100);
        $this->assertEquals(
            'The random number is: ' . $random_numer,
            Librarian::call('mysli/backend', 'say_number', [$random_numer])
        );
    }

    public function test_is_enabled()
    {
        $this->assertTrue(
            Librarian::is_enabled('mysli/backend')
        );
        $this->assertFalse(
            Librarian::is_enabled('avrelia/writter')
        );
        $this->assertFalse(
            Librarian::is_enabled('avrelia/non_existant')
        );
    }
}
