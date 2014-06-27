<?php

namespace Mysli\Pkgm;

include(__DIR__.'/_common.php');

Generator::drop_packages();
Generator::generate_packages();

class PkgmTest extends \PHPUnit_Framework_TestCase
{
    private $pkgm;

    public function __construct()
    {
        \Core\FS::dir_create(datpath('pkgm'));
        file_put_contents(datpath('pkgm/registry.json'), json_encode(['enabled' => [], 'roles' => []]));
        $this->pkgm = new Pkgm();
    }

    public function test_call()
    {
        $result = $this->pkgm->call('avrelia/users->say_hi', ['Marko']);
        $this->assertEquals(
            'Hi, Marko',
            $result
        );
    }

    public function test_call_static()
    {
        $result = $this->pkgm->call('avrelia/users::say_hello', ['Marko', 1440]);
        $this->assertEquals(
            'Hello Marko! Your number is: 1440.',
            $result
        );
    }

    public function test_call_specific()
    {
        $result = $this->pkgm->call('avrelia/users/users->say_hi', ['Marko']);
        $this->assertEquals(
            'Hi, Marko',
            $result
        );
    }
}
