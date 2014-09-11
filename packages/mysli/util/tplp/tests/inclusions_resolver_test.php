<?php

namespace Mysli\Tplp;

include(__DIR__.'/../exceptions/parser_exception.php');
include(__DIR__.'/../inclusions_resolver.php');
include(__DIR__.'/../../core/core.php'); // CORE needed for utility!
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);

class InclusionsResolverTest extends \PHPUnit_Framework_TestCase
{
    private function instance_resolve($templates)
    {
        $instance = new \Mysli\Tplp\InclusionsResolver($templates);
        return $instance->resolve();
    }

    public function test_resolve_basic()
    {
        $this->assertEquals(
            "Hello!\nWorld!",
            $this->instance_resolve([
            'main' =>
                "Hello!\n::use side",
            'side' =>
                "World!"
            ])['main']
        );
    }

    public function test_resolve_chain()
    {
        $this->assertEquals(
            "Hello!\nWorld!\nMoon!",
            $this->instance_resolve([
            'main' =>
                "Hello!\n::use side",
            'side' =>
                "World!\n::use moon",
            'moon' =>
                'Moon!'
            ])['main']
        );
    }

    public function test_resolve_master()
    {
        $this->assertEquals(
            "Top\nHello world!\nBottom",
            $this->instance_resolve([
            'layout' =>
                "Top\n::yield\nBottom",
            'contents' =>
                "Hello world!\n::use layout as master"
            ])['contents']
        );
    }

    public function test_resolve_master_chain()
    {
        $this->assertEquals(
            "Top\nHello world!\nBottom Content!\nBottom Content!\nBottom\nBottom Content!",
            $this->instance_resolve([
            'layout' =>
                "Top\n::yield\nBottom\n::use bottom",
            'contents' =>
                "Hello world!\n::use layout as master\n::use bottom\n::use bottom",
            'bottom' =>
                'Bottom Content!'
            ])['contents']
        );
    }
}
