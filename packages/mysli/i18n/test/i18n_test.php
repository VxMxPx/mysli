<?php

namespace Mysli;

include(__DIR__.'/../i18n.php');    // Include self
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);

class DummyConfig { public function get() { return; } }

class I18nTest extends \PHPUnit_Framework_TestCase
{
    protected $i18n;

    public function __construct()
    {
        $this->i18n = new \Mysli\I18n(['test/package', null], new DummyConfig());

        // Always create fresh cache
        $this->i18n->cache_create();
    }

    public function test_instance()
    {
        $this->assertInstanceOf('\\Mysli\\I18n', $this->i18n);
    }
}
