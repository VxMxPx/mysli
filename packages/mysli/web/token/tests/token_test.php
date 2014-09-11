<?php

namespace Mysli\Token;

include(__DIR__.'/generator.php');
generate_test_data();

include(__DIR__.'/../../core/core.php');
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);
include(__DIR__.'/../token.php');


class TokenTest extends \PHPUnit_Framework_TestCase
{
    protected function get_instace() {
        return new Token();
    }

    protected function get_clean_instance() {
        file_put_contents(datpath('mysli.token/registry.json'), '');
        return $this->get_instace();
    }

    public function test_instance()
    {
        $this->assertInstanceOf('\\Mysli\\Token\\Token', $this->get_clean_instance());
    }

    public function test_create_get()
    {
        $token = $this->get_clean_instance();
        $id = $token->create(12);
        $this->assertEquals(
            12,
            $token->get($id)
        );
    }

    public function test_create_get_expired()
    {
        $token = $this->get_clean_instance();
        $id = $token->create(12, -10);
        $this->assertFalse($token->get($id));
    }

    public function test_write()
    {
        $token = $this->get_clean_instance();
        $id = $token->create(12);
        $registry = file_get_contents(datpath('mysli.token/registry.json'));
        $registry = json_decode($registry, true);
        $this->assertTrue(isset($registry['tokens'][$id]));
    }

    public function test_remove()
    {
        $token = $this->get_clean_instance();
        $id = $token->create(12);
        $this->assertEquals(
            12,
            $token->get($id)
        );
        $token->remove($id);
        $this->assertFalse($token->get($id));
    }

    public function test_cleanup()
    {
        $token = $this->get_clean_instance();

        $token->create(11, -10);
        $id12 = $token->create(12);
        $id13 = $token->create(13, -10);
        $token->create(14, -10);

        $this->assertEquals(3, $token->cleanup());
        $this->assertEquals(
            12,
            $token->get($id12)
        );
        $this->assertFalse($token->get($id13));
    }

}
