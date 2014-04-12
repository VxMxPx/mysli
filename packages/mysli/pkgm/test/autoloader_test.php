<?php

namespace Mysli\Pkgm;

include(__DIR__.'/generator.php');
include(__DIR__.'/../util.php');
include(__DIR__.'/../autoloader.php');
include(__DIR__.'/../../core/core.php');
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy/private'),
    realpath(__DIR__.'/dummy/packages')
);

class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        Generator::drop_packages();
        Generator::generate_packages();

        spl_autoload_register(['\\Mysli\\Pkgm\\Autoloader', 'load']);
    }

    public function test_load()
    {
        $this->assertFalse(class_exists('\\Mysliio\\Core\\Core', false));
        $this->assertTrue(Autoloader::load('Mysliio\\Core\\Core'));
        $this->assertTrue(class_exists('\\Mysliio\\Core\\Core', false));
    }
}
