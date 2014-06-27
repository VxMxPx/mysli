<?php

namespace Mysli\Pkgm;

include(__DIR__.'/_common.php');

Generator::drop_packages();
Generator::generate_packages();

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    private $pkgm;

    public function __construct()
    {
        \Core\FS::dir_create(datpath('pkgm'));
        file_put_contents(datpath('pkgm/registry.json'), json_encode(['enabled' => [], 'roles' => []]));
        $this->pkgm = new Pkgm();

        $dependencies = $this->pkgm->registry()->list_dependencies('avrelia/dash', true)['disabled'];
        foreach ($dependencies as $dependency => $version)
            $this->pkgm->control($dependency)->enable();

        $this->pkgm->control('avrelia/dash')->enable();
        $this->pkgm->control('mysliio/pkgm')->enable();
    }

    public function test_product()
    {
        $this->assertInstanceOf(
            '\\Avrelia\\Dash\\Dash',
            $this->pkgm->factory('avrelia/dash')->produce()
        );
    }

    public function test_product_subclass()
    {
        $this->assertInstanceOf(
            '\\Mysliio\\Pkgm\\Script\\Pkgm',
            $this->pkgm->factory('mysliio/pkgm')->produce('script/pkgm')
        );
    }

    public function test_can_produce()
    {
        $this->assertTrue(
            $this->pkgm->factory('avrelia/dash')->can_produce()
        );
    }

    public function test_can_produce_not()
    {
        $this->assertFalse(
            $this->pkgm->factory('avrelia/dash')->can_produce('bad/subclass')
        );
    }
}
