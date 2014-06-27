<?php

namespace Mysli\Pkgm;

include(__DIR__.'/_common.php');

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function test_add_get()
    {
        $cache = new Cache();
        $cache->add('mysliio/core/core', 42);
        $this->assertEquals(
            42,
            $cache->get('mysliio/core/core')
        );
    }

    public function test_add_has()
    {
        $cache = new Cache();
        $cache->add('mysliio/core/core', 42);
        $this->assertTrue(
            $cache->has('mysliio/core/core')
        );
    }

    public function test_add_remove_has()
    {
        $cache = new Cache();
        $cache->add('mysliio/core/core', 42);
        $this->assertTrue(
            $cache->has('mysliio/core/core')
        );
        $cache->remove('mysliio/core/core');
        $this->assertFalse(
            $cache->has('mysliio/core/core')
        );
    }
}
