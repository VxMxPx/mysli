<?php

namespace Mysli\Event;

include(__DIR__.'/../../core/core.php');
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);
include(__DIR__.'/../event.php');

class EventTest extends \PHPUnit_Framework_TestCase
{
    protected function get_instance($empty = true)
    {
        if ($empty) {
            file_put_contents(pkgpath('event/registry.json'), '[]');
        }

        return new Event($this->getMock('Mysli\\Pkgm\\Pkgm'));
    }

    public function test_register()
    {
        $event = $this->get_instance();
        $this->assertTrue($event->register(
            'mysli/event/test/event_test::test_register',
            'vendor/package::method'
        ));

        $event2 = $this->get_instance(false);
        $data = $event2->dump();

        $this->assertEquals(
            'vendor/package::method',
            $data[0]['mysli/event/test/event_test::test_register'][0]
        );
    }

    public function test_unregister()
    {
        $event = $this->get_instance();
        $this->assertTrue($event->register(
            'mysli/event/test/event_test::test_unregister',
            'vendor/package::method_one'
        ));
        $this->assertTrue($event->register(
            'mysli/event/test/event_test::test_unregister',
            'vendor/package::method_two'
        ));

        $data1 = $event->dump();

        $this->assertCount(
            2,
            $data1[0]['mysli/event/test/event_test::test_unregister']
        );

        $event->unregister(
            'mysli/event/test/event_test::test_unregister',
            'vendor/package::method_one'
        );

        $event2 = $this->get_instance(false);
        $data2 = $event2->dump();

        $this->assertEquals(
            'vendor/package::method_two',
            $data2[0]['mysli/event/test/event_test::test_unregister'][1]
        );
        $this->assertCount(
            1,
            $data2[0]['mysli/event/test/event_test::test_unregister']
        );
    }

    public function test_on()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event->on($event_name, 'vendor/package::method');
        $available = $event->dump()[0];

        $this->assertArrayHasKey($event_name, $available);
    }

    public function test_off_name()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event_id = $event->on($event_name, 'vendor/package::method');
        $event->off($event_name, 'vendor/package::method');
        $available = $event->dump()[0];

        $this->assertFalse(isset($available[$event_name]));
    }

    public function test_off_id()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event_id = $event->on($event_name, 'vendor/package::method');
        $event->off($event_name, $event_id);
        $available = $event->dump()[0];
        $this->assertFalse(isset($available[$event_name]));
    }

    public function test_off_id_two()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event_id_one = $event->on($event_name, 'vendor/package::method');
        $event_id_two = $event->on($event_name, 'vendor/package::method_two');
        $event->off($event_name, $event_id_one);
        $available = $event->dump()[0];

        $this->assertTrue(isset($available[$event_name][$event_id_two]));
    }

    public function test_trigger()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event->on($event_name, function (&$result) {
            $result = 'Hello World!';
        });

        $result = '';
        $event->trigger($event_name, [&$result]);

        $this->assertEquals('Hello World!', $result);
    }

    public function test_trigger_regex()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event->on('*/event/*/event_test::test_register', function (&$result) {
            $result = 'Hello World!';
        });

        $result = '';
        $event->trigger($event_name, [&$result]);

        $this->assertEquals('Hello World!', $result);
    }

    public function test_trigger_many()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event->on($event_name, function (&$result) {
            $result .= 'One ';
        });
        $event->on($event_name, function (&$result) {
            $result .= 'Two ';
        });
        $event->on($event_name, function (&$result) {
            $result .= 'Three ';
        });
        $event->on($event_name, function (&$result) {
            $result .= 'Four!';
        });

        $result = '';
        $event->trigger($event_name, [&$result]);

        $this->assertEquals('One Two Three Four!', $result);
    }

    public function test_trigger_many_params()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event->on($event_name, function (&$first, &$second) {
            $first = 'Hello World!';
            $second = 'Hello Moon!';
        });

        $first = '';
        $second = '';
        $event->trigger($event_name, [&$first, &$second]);

        $this->assertEquals('Hello World!', $first);
        $this->assertEquals('Hello Moon!', $second);
    }


    public function test_trigger_many_params_no_ref()
    {
        $event = $this->get_instance();
        $event_name = 'mysli/event/test/event_test::test_register';

        $event->on($event_name, function (&$first, $second) {
            $first = 'Hello World!';
            $second = 'Hello Moon!';
        });

        $first = '';
        $second = 'Nop!';
        $event->trigger($event_name, [&$first, $second]);

        $this->assertEquals('Hello World!', $first);
        $this->assertEquals('Nop!', $second);
    }
}
