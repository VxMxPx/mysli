<?php

namespace Mysli\Core\Lib;

include(__DIR__.'/../../lib/arr.php');
include(__DIR__.'/../../lib/str.php');

define('CHAR_APOSTROPHE', "'");
define('CHAR_QUOTE', '"');
define('CHAR_SPACE', ' ');
define('CHAR_SLASH', '/');
define('CHAR_BACKSLASH', '\\');
define('STRING_CAMELCASE', 'string-camelcase');
define('STRING_UNDERSCORE', 'string-underscore');

class ArrTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_one_dimension()
    {
        $multi = [
            ['id' => 15, 'name' => 'Jack'],
            ['id' => 42, 'name' => 'Neo']
        ];
        $this->assertEquals(
            ['Jack', 'Neo'],
            Arr::to_one_dimension($multi, 'name')
        );
        $this->assertEquals(
            [15, 42],
            Arr::to_one_dimension($multi, 'id')
        );
        $this->assertEquals(
            [15 => 'Jack', 42 => 'Neo'],
            Arr::to_one_dimension($multi, 'name', 'id')
        );
        $this->assertEquals(
            [],
            Arr::to_one_dimension([], 'name')
        );
        $this->assertEquals(
            ['Nina'],
            Arr::to_one_dimension([
                ['name'    => 'Nina'],
                ['no-name' => 'Hi there!']
            ], 'name')
        );
        $this->assertEquals(
            [
                0 => 'Nina',
                2 => 'Andrej',
                1 => 'Tina',
            ],
            Arr::to_one_dimension([
                ['name'    => 'Nina', 'id' => 0],
                ['no-name' => 'Hi there!'],
                ['name'    => 'Andrej'],
                ['name'    => 'Tina', 'id' => 1]
            ], 'name', 'id')
        );
        $this->assertEquals(
            [
                0 => 'Andrej',
                1 => 'Tina',
                2 => 'Lana'
            ],
            Arr::to_one_dimension([
                ['name'    => 'Lana'],
                ['name'    => 'Nina',   'id' => 0],
                ['no-name' => 'Hi there!'],
                ['name'    => 'Andrej', 'id' => 0],
                ['name'    => 'Tina',   'id' => 1]
            ], 'name', 'id')
        );
    }

    public function test_implode_keys()
    {
        $this->assertEquals(
            '1::2::3::4',
            Arr::implode_keys('::', [1 => '', 2 => '', 3 => '', 4 => ''])
        );
        $this->assertEquals(
            '0::1::2::3',
            Arr::implode_keys('::', [1, 2, 3, 4])
        );
        $this->assertEquals(
            '',
            Arr::implode_keys('::', [])
        );
        $this->assertEquals(
            '0::1::2',
            Arr::implode_keys('::', [[1, 2], 3, 4])
        );
    }

    public function test_key_from_sub()
    {
        $data = [
            ['id' => 12, 'age' => 25],
            ['id' => 20, 'age' => 30]
        ];

        $this->assertEquals(
            [
                12 => ['id' => 12, 'age' => 25],
                20 => ['id' => 20, 'age' => 30]
            ],
            Arr::key_from_sub($data, 'id')
        );
        $this->assertEquals([], Arr::key_from_sub([], 'id'));

        // Duplicated ID
        $data[] = ['id' => 12, 'age' => 40];
        $this->assertEquals(
            [
                12     => ['id' => 12, 'age' => 25],
                20     => ['id' => 20, 'age' => 30],
                '12_2' => ['id' => 12, 'age' => 40]
            ],
            Arr::key_from_sub($data, 'id')
        );
        $this->assertEquals(
            [
                12     => ['id' => 12, 'age' => 40],
                20     => ['id' => 20, 'age' => 30]
            ],
            Arr::key_from_sub($data, 'id', true, true)
        );

        // No key set
        $data[] = ['age' => 82];
        $this->assertEquals(
            [
                0      => ['age' => 82],
                12     => ['id' => 12, 'age' => 25],
                20     => ['id' => 20, 'age' => 30],
                '12_2' => ['id' => 12, 'age' => 40]
            ],
            Arr::key_from_sub($data, 'id')
        );
        $this->assertEquals(
            [
                12     => ['id' => 12, 'age' => 25],
                20     => ['id' => 20, 'age' => 30],
                '12_2' => ['id' => 12, 'age' => 40]
            ],
            Arr::key_from_sub($data, 'id', false)
        );
    }

    public function test_remove_empty()
    {
        $expectation = ['Anja', 'Janja', 'Sanja'];

        $this->assertEquals(
            $expectation,
            Arr::remove_empty(['Anja', 'Janja', 'Sanja'])
        );

        $this->assertEquals(
            $expectation,
            Arr::remove_empty(['Anja', null, '     ', '', 'Janja', 'Sanja'])
        );

        $this->assertEquals(
            ['Anja', false, 0, '0', 'Janja', 'Sanja'],
            Arr::remove_empty(['Anja', false, 0, '0', 'Janja', 'Sanja'])
        );

        $this->assertEquals(
            ['Anja', [1, 2], 'Janja', 'Sanja'],
            Arr::remove_empty(['Anja', [null, 1, 2], 'Janja', 'Sanja'])
        );

        $this->assertEquals(
            ['a' => 'Anja', 'c' => 'Sanja'],
            Arr::remove_empty(['a' => 'Anja', 'b' => '', 'c' => 'Sanja'])
        );

        $this->assertEquals(
            ['a' => 'Anja', 'c' => 'Sanja'],
            Arr::remove_empty(['a' => 'Anja', 'b' => [], 'c' => 'Sanja'])
        );

        $this->assertEquals(
            ['a' => 'Anja', 'c' => 'Sanja'],
            Arr::remove_empty(['a' => 'Anja', 'b' => ['', null], 'c' => 'Sanja'])
        );
    }

    public function test_is_associative()
    {
        $this->assertFalse(Arr::is_associative([0, 1, 2]));
        $this->assertFalse(Arr::is_associative([1]));
        $this->assertFalse(Arr::is_associative([]));
        $this->assertFalse(Arr::is_associative([['b' => 'c'], []]));
        $this->assertTrue(Arr::is_associative(['k' => 0, 2]));
    }

    public function test_explode_to_key()
    {
        $this->assertEquals(
            ['name' => 'Marko', 'id' => '12', 'age' => '23'],
            Arr::explode_to_key(['name.Marko', 'id.12', 'age.23'], '.')
        );
        $this->assertEquals(
            ['name' => 'Marko', 'id' => '12', 'age' => '23'],
            Arr::explode_to_key(['name==>Marko', 'id==>12', 'age==>23'], '==>')
        );
        $this->assertEquals(
            ['name' => 'Marko.Von.Eraofmorn', 'id' => '12', 'age' => '23'],
            Arr::explode_to_key(['name.Marko.Von.Eraofmorn', 'id.12', 'age.23'], '.')
        );
        $this->assertEquals(
            [],
            Arr::explode_to_key([], '.')
        );
        $this->assertEquals(
            ['Marko', 'id' => '12', 'age' => '23'],
            Arr::explode_to_key(['Marko', 'id.12', 'age.23'], '.', false)
        );
        $this->assertEquals(
            ['id' => '12', 'age' => '23'],
            Arr::explode_to_key(['Marko', 'id.12', 'age.23'], '.')
        );
    }

    public function test_clean_keys()
    {
        $in  = [
            'hello world  ' => 0,
            ' hEllo-new-WOrld' => 0,
            'ščć' => 0,
            '------' => 'a',
            12 => 0,
            1 => 'b',
            '' => 'c'
        ];

        $this->assertEquals(
            [
                'helloWorld' => 0,
                'helloNewWorld' => 0,
                'scc' => 0,
                0 => 'a',
                12 => 0,
                1 => 'b',
                2 => 'c'
            ],
            Arr::clean_keys($in, STRING_CAMELCASE)
        );
        $this->assertEquals(
            [
                'hello_world' => 0,
                'hello_new_world' => 0,
                'scc' => 0,
                0 => 'a',
                12 => 0,
                1 => 'b',
                2 => 'c'
            ],
            Arr::clean_keys($in, STRING_UNDERSCORE)
        );
    }

    public function test_is_empty()
    {
        $this->assertTrue(Arr::is_empty([]));
        $this->assertTrue(Arr::is_empty(0));
        $this->assertTrue(Arr::is_empty(''));
        $this->assertTrue(Arr::is_empty(false));
        $this->assertTrue(Arr::is_empty(true));
        $this->assertFalse(Arr::is_empty(['']));
        $this->assertFalse(Arr::is_empty([[]]));
        $this->assertFalse(Arr::is_empty([0]));
    }

    public function test_has_key()
    {
        $this->assertFalse(Arr::has_key('', 'name'));
        $this->assertFalse(Arr::has_key([], 'name'));
        $this->assertFalse(Arr::has_key(['name'], 'name'));
        $this->assertTrue(Arr::has_key(['name' => 0], 'name'));
        $this->assertTrue(Arr::has_key([1], 0));
        $this->assertTrue(Arr::has_key([1, 1, 1], 2));
    }
}