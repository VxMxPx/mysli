<?php

namespace Mysli;

include(__DIR__.'/../event.php');        // Include self
include(__DIR__.'/../../core/core.php'); // Mysli CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);

class EventTest extends \PHPUnit_Framework_TestCase
{
    protected function get_instance()
    {
        return new Event(null);
    }

    protected function reset_file()
    {
        file_put_contents(libpath('event/registry.json'), '[]');
    }

    public function test_register()
    {
        $this->reset_file();
        $event = $this->get_instance();
        $this->assertTrue($event->register(
            'mysli/event/test/event_test::test_register',
            'vendor/library::method'
        ));

        $event2 = $this->get_instance();
        $data = $event2->dump();

        $this->assertEquals(
            'vendor/library::method',
            $data[0]['mysli/event/test/event_test::test_register']['medium'][0]
        );
    }

    public function test_unregister()
    {
        $this->reset_file();
        $event = $this->get_instance();
        $this->assertTrue($event->register(
            'mysli/event/test/event_test::test_unregister',
            'vendor/library::method_one'
        ));
        $this->assertTrue($event->register(
            'mysli/event/test/event_test::test_unregister',
            'vendor/library::method_two'
        ));

        $data1 = $event->dump();

        $this->assertCount(
            2,
            $data1[0]['mysli/event/test/event_test::test_unregister']['medium']
        );

        $event->unregister(
            'mysli/event/test/event_test::test_unregister',
            'vendor/library::method_one'
        );

        $event2 = $this->get_instance();
        $data2 = $event2->dump();

        $this->assertEquals(
            'vendor/library::method_two',
            $data2[0]['mysli/event/test/event_test::test_unregister']['medium'][1]
        );
        $this->assertCount(
            1,
            $data2[0]['mysli/event/test/event_test::test_unregister']['medium']
        );
    }

    public function test_on()
    {
        $this->reset_file();
        $event = $this->get_instance();

        $event->on(
            'mysli/event/test/event_test::test_register',
            function (&$result) { $result = 'Hello World!'; }
        );

        $result = '';
        $event->trigger('mysli/event/test/event_test::test_register', $result);

        $this->assertEquals('Hello World!', $result);
    }

    public function test_off()
    {
        $this->reset_file();
        $event = $this->get_instance();

        $event_id = $event->on(
            'mysli/event/test/event_test::test_register',
            function (&$result) { $result = 'Hello World!'; }
        );

        $event->off('mysli/event/test/event_test::test_register', $event_id);

        $result = 'Nop';
        $event->trigger('mysli/event/test/event_test::test_register', $result);

        $this->assertEquals('Nop', $result);
    }
}
