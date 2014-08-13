<?php

namespace Mysli\Assets;

include(__DIR__.'/../../core/core.php');
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);
include(__DIR__.'/../util.php');
include(__DIR__.'/../assets.php');

class AssetsTest extends \PHPUnit_Framework_TestCase
{
    protected function get_instance($debug = true)
    {
        $dummy_web = $this->getMock('Mysli\\Web\\Web', ['url']);
        $dummy_web
            ->method('url')
            ->will($this->returnValue(null));

        $dummy_config = $this->getMock('Mysli\\Config\\Config', ['get']);
        $dummy_config
            ->method('get')
            ->will($this->returnValue($debug));

        return new Assets($dummy_web, $dummy_config);
    }

    public function test_instance()
    {
        $this->assertInstanceOf('\\Mysli\\Assets\\Assets', $this->get_instance());
    }

    public function test_get_tags()
    {
        dump($this->get_instance(true)->get_tags('js', 'mysli/test'));
        $this->assertEquals(
            [],
            $this->get_instance(true)->get_tags('js', 'mysli/test')
        );
    }
}
