<?php

namespace Mysli\Core\Util;

include(__DIR__.'/../../core.php');
new \Mysli\Core(
    __DIR__.'/../dummy/public',
    __DIR__.'/../dummy/libraries',
    __DIR__.'/../dummy/data'
);

class ArrTest extends \PHPUnit_Framework_TestCase
{
    public static function arr2d_provider()
    {
        return [[
            [
                ['id' => 15, 'name' => 'Jack'],
                ['id' => 42, 'name' => 'Neo']
            ]
        ]];
    }

    public static function arr2d_complex_provider()
    {
        return [[
            [
                ['name'    => 'Lana'],
                ['name'    => 'Nina',   'id' => 0],
                ['name'    => 'Andrej', 'id' => 0],
                ['name'    => 'Tina',   'id' => 1]
            ]
        ]];
    }

    /**
     * Test Arr::to_one_dimension()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_to_one_dimension($data)
    {
        $this->assertEquals(
            ['Jack', 'Neo'],
            Arr::to_one_dimension($data, 'name')
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Test Arr::to_one_dimension()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_to_one_dimension_with_key($data)
    {
        $this->assertEquals(
            [15 => 'Jack', 42 => 'Neo'],
            Arr::to_one_dimension($data, 'name', 'id')
        );
    }

    /**
     * Test Arr::to_one_dimension()
     *
     * @test
     */
    public function test_to_one_dimension_empty()
    {
        $this->assertEquals(
            [],
            Arr::to_one_dimension([], 'name')
        );
    }

    /**
     * Test Arr::to_one_dimension()
     *
     * @test
     * @dataProvider arr2d_complex_provider
     */
    public function test_to_one_dimension_rewrite($data)
    {
        $expectation = [
            0 => 'Andrej',
            1 => 'Tina',
            2 => 'Lana'
        ];

        $this->assertEquals(
            $expectation,
            Arr::to_one_dimension($data, 'name', 'id')
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Test Arr::implode_keys()
     *
     * @test
     */
    public function test_implode_keys()
    {
        $this->assertEquals(
            '0::1::2::3',
            Arr::implode_keys('::', [1, 2, 3, 4])
        );
    }

    /**
     * Test Arr::implode_keys()
     *
     * @test
     */
    public function test_implode_keys_associative()
    {
        $this->assertEquals(
            '1::2::3::4',
            Arr::implode_keys('::', [1 => '', 2 => '', 3 => '', 4 => ''])
        );
    }

    /**
     * Test Arr::implode_keys()
     *
     * @test
     */
    public function test_implode_keys_empty()
    {
        $this->assertEquals(
            '',
            Arr::implode_keys('::', [])
        );
    }

    /**
     * Test Arr::implode_keys()
     *
     * @test
     */
    public function test_implode_keys_multi_dimension()
    {
        $this->assertEquals(
            '0::1::2',
            Arr::implode_keys('::', [[1, 2], 3, 4])
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Test Arr::key_from_sub()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_key_from_sub($data)
    {
        $expectation = [
            15 => ['id' => 15, 'name' => 'Jack'],
            42 => ['id' => 42, 'name' => 'Neo']
        ];

        $this->assertEquals(
            $expectation,
            Arr::key_from_sub($data, 'id')
        );
    }

    /**
     * Test Arr::key_from_sub()
     *
     * @test
     */
    public function test_key_from_sub_empty()
    {
        $this->assertEquals([], Arr::key_from_sub([], 'id'));
    }

    /**
     * Test Arr::key_from_sub()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_key_from_sub_duplicated_add($data)
    {
        $data[] = ['id' => 42, 'name' => 'Anna'];

        $expectation = [
            15     => ['id' => 15, 'name' => 'Jack'],
            42     => ['id' => 42, 'name' => 'Neo'],
            '42_2' => ['id' => 42, 'name' => 'Anna']
        ];

        $this->assertEquals(
            $expectation,
            Arr::key_from_sub($data, 'id')
        );
    }

    /**
     * Test Arr::key_from_sub()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_key_from_sub_duplicated_rewrite($data)
    {
        $data[] = ['id' => 42, 'name' => 'Anna'];

        $expectation = [
            15     => ['id' => 15, 'name' => 'Jack'],
            42     => ['id' => 42, 'name' => 'Anna'],
        ];

        $this->assertEquals(
            $expectation,
            Arr::key_from_sub($data, 'id', true, true)
        );
    }

    /**
     * Test Arr::key_from_sub()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_key_from_sub_missing_key_add($data)
    {
        $data[] = ['name' => 'Anna'];

        $expectation = [
            0      => ['name' => 'Anna'],
            15     => ['id' => 15, 'name' => 'Jack'],
            42     => ['id' => 42, 'name' => 'Neo'],
        ];

        $this->assertEquals(
            $expectation,
            Arr::key_from_sub($data, 'id')
        );
    }

    /**
     * Test Arr::key_from_sub()
     *
     * @test
     * @dataProvider arr2d_provider
     */
    public function test_key_from_sub_missing_key_ignore($data)
    {
        $data[] = ['name' => 'Anna'];

        $expectation = [
            15     => ['id' => 15, 'name' => 'Jack'],
            42     => ['id' => 42, 'name' => 'Neo'],
        ];

        $this->assertEquals(
            $expectation,
            Arr::key_from_sub($data, 'id', false)
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Test Arr::remove_empty()
     *
     * @test
     */
    public function test_remove_empty()
    {
        $expectation = ['Anja', 'Janja', 'Sanja'];

       $this->assertEquals(
            $expectation,
            Arr::remove_empty(['Anja', null, '  ', '', 'Janja', 'Sanja'])
        );
    }

    /**
     * Test Arr::remove_empty()
     *
     * @test
     */
    public function test_remove_empty_multi_dimensional()
    {
        $this->assertEquals(
            ['Anja', [1, 2], 'Janja', 'Sanja'],
            Arr::remove_empty(['Anja', [null, 1, 2], [], 'Janja', 'Sanja'])
        );
    }

    /**
     * Test Arr::remove_empty()
     *
     * @test
     */
    public function test_remove_empty_with_keys()
    {
        $this->assertEquals(
            ['a' => 'Anja', 'c' => 'Sanja'],
            Arr::remove_empty(['a' => 'Anja', 'b' => ['', 'b' => null], 'c' => 'Sanja', 'd' => ''])
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Test Arr::is_associative()
     *
     * @test
     */
    public function test_is_associative()
    {
        $this->assertFalse(Arr::is_associative([0, 1, 2]));
        $this->assertFalse(Arr::is_associative([1]));
        $this->assertFalse(Arr::is_associative([]));
        $this->assertFalse(Arr::is_associative([['b' => 'c'], []]));

        $this->assertTrue(Arr::is_associative(['k' => 0, 2]));
    }

    // -------------------------------------------------------------------------

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
            Arr::clean_keys($in, Arr::CAMELCASE)
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
            Arr::clean_keys($in, Arr::UNDERSCORE)
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

    public function test_has_keys()
    {
        $this->assertTrue(Arr::has_keys(
            ['name' => 'Marko', 'age' => 29],
            ['name', 'age']
        ));

        $this->assertFalse(Arr::has_keys(
            ['name' => 'Marko', 'age' => 29],
            ['name', 'age', 'email']
        ));

        $this->assertFalse(Arr::has_keys([], []));
        $this->assertFalse(Arr::has_keys(['name' => 'Marko'], []));
        $this->assertFalse(Arr::has_keys([], ['name']));
    }

    public function test_element()
    {
        $input = [
            'name' => 'Marko',
            'age'  => 29
        ];

        $this->assertEquals('Marko', Arr::element(
            'name',
            $input)
        );
        $this->assertFalse(Arr::element(
            'email',
            $input)
        );
        $this->assertEquals('marko@example.dev', Arr::element(
            'email',
            $input,
            'marko@example.dev')
        );
        $this->assertEquals('Marko', Arr::element(
            0,
            ['Marko', 'Inna'])
        );
    }

    public function test_elements()
    {
        $input = [
            'id'   => 12,
            'name' => 'Marko',
            'age'  => 29,
        ];

        $this->assertEquals(['name' => 'Marko', 'age' => 29], Arr::elements(
            ['name', 'age'],
            $input)
        );
        $this->assertEquals(['name' => 'Marko'], Arr::elements(
            ['name'],
            $input)
        );
        $this->assertEquals(
            [
                'name' => 'Marko',
                'age' => 29,
                'email' => false
            ],
            Arr::elements(
                ['name', 'age', 'email'],
                $input
            )
        );
        $this->assertEquals(
            [
                'name' => 'Marko',
                'age' => 29,
                'email' => null
            ],
            Arr::elements(
                ['name', 'age', 'email'],
                $input,
                null
            )
        );
        $this->assertEquals(
            [
                'name' => 'Marko',
                'age' => 29,
                'email' => 'default@domain.dev'
            ],
            Arr::elements(
                ['name', 'age', 'email'],
                $input,
                ['email' => 'default@domain.dev']
            )
        );
        $this->assertEquals(
            [
                'name' => 'Marko',
                'age' => 29,
                'email' => false,
                'web' => 'http://'
            ],
            Arr::elements(
                ['name', 'age', 'email', 'web'],
                $input,
                ['web' => 'http://']
            )
        );
        $this->assertEquals(
            [
                'x' => -1,
                'y' => -1
            ],
            Arr::elements(
                ['x', 'y'],
                $input,
                -1
            )
        );
    }

    public function test_random_element()
    {
        $input  = ['Anja', 'Sanja', 'Manja', 'Janja', 'Tanja'];
        $bucket = ['Anja' => 0, 'Sanja' => 0, 'Manja' => 0, 'Janja' => 0, 'Tanja' => 0];
        for ($i=0; $i < 100; $i++) {
            $bucket[Arr::random_element($input)]++;
        }
        foreach ($bucket as $key => $value) {
            $this->assertTrue($value > 8, "Expected value for {$key} ({$value}) is more than 8.");
        }
    }

    public function test_merge()
    {
        $this->assertEquals(
            [1,2,3,4],
            Arr::merge([1,2],[3,4])
        );

        $this->assertEquals(
            ['a' => 1, 2, 3, 4],
            Arr::merge(['a' => 1,2],[3,4])
        );

        $this->assertEquals(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
                'd' => 4
            ],
            Arr::merge(
                [
                    'a' => 1,
                    'b' => 2
                ],
                [
                    'c' => 3,
                    'd' => 4
                ]
            )
        );
        $this->assertEquals(
            [
                'a' => 1,
                'b' => [
                    'b1' => 21,
                    'b2' => 22
                ],
                'c' => 3,
                'd' => 4
            ],
            Arr::merge(
                [
                    'a' => 1,
                    'b' => [
                        'b1' => 21,
                        'b2' => 22
                    ]
                ],
                [
                    'c' => 3,
                    'd' => 4
                ]
            )
        );
        $this->assertEquals(
            [
                'a' => 1,
                'b' => [
                    'b1' => 21,
                    'b2' => 22,
                    'b3' => 23
                ],
                'c' => 3,
                'd' => 4
            ],
            Arr::merge(
                [
                    'a' => 1,
                    'b' => [
                        'b1' => 21,
                        'b2' => 22
                    ]
                ],
                [
                    'b' => [
                        'b3' => 23
                    ],
                    'c' => 3,
                    'd' => 4
                ]
            )
        );
        $this->assertEquals(
            [
                'a' => 1,
                'b' => [
                    'b1' => 24,
                    'b2' => 22,
                    'b3' => 23
                ],
                'c' => 3,
                'd' => 4
            ],
            Arr::merge(
                [
                    'a' => 1,
                    'b' => [
                        'b1' => 21,
                        'b2' => 22
                    ]
                ],
                [
                    'b' => [
                        'b1' => 24,
                        'b3' => 23
                    ],
                    'c' => 3,
                    'd' => 4
                ]
            )
        );
        $this->assertEquals(
            [
                'a' => 1,
                'b' => [
                    'b1' => 24,
                    'b2' => 22,
                    'b3' => [
                        'b31' => 231,
                        'b32' => 222,
                        'b33' => 233
                    ]
                ],
                'c' => 3,
                'd' => 4
            ],
            Arr::merge(
                [
                    'a' => 1,
                    'b' => [
                        'b1' => 21,
                        'b2' => 22,
                        'b3' => [
                            'b31' => 220,
                            'b32' => 222
                        ]
                    ]
                ],
                [
                    'b' => [
                        'b1' => 24,
                        'b3' => [
                            'b31' => 231,
                            'b33' => 233
                        ]
                    ],
                    'c' => 3,
                    'd' => 4
                ]
            )
        );
        $this->assertEquals(
            [
                'a' => 1,
                'b' => [
                    'b1' => 24,
                    'b2' => 22,
                    'b3' => [
                        'b31' => 231,
                        'b32' => [1, 2, 3, 4, 5, 6]
                    ]
                ],
                'c' => 3,
                'd' => 4
            ],
            Arr::merge(
                [
                    'a' => 1,
                    'b' => [
                        'b1' => 21,
                        'b2' => 22,
                        'b3' => [
                            'b31' => 220,
                            'b32' => [1, 2, 3]
                        ]
                    ]
                ],
                [
                    'b' => [
                        'b1' => 24,
                        'b3' => [
                            'b31' => 231,
                            'b32' => [4, 5, 6]
                        ]
                    ],
                    'c' => 3,
                    'd' => 4
                ]
            )
        );
    }

    public function test_implode_true()
    {
        $this->assertEquals(
            '1.2.3',
            Arr::implode_true(
                '.',
                [0, 1, 2, 3]
            )
        );
        $this->assertEquals(
            'default',
            Arr::implode_true(
                '.',
                [0, false, '0', null, '', []],
                'default'
            )
        );
        $this->assertEquals(
            'hello world',
            Arr::implode_true(
                '',
                ['h', 'e', 'l', 'l', 'o', ' ', 0, false, 'w', 'o', 'r', 'l', 'd'],
                'default'
            )
        );
        $this->assertEquals(
            'hello world',
            Arr::implode_true(
                '',
                [['h', 'e', ['l', 'l'], 'o'], [' ', 0, false], [false, 0, [0, []]], ['w', 'o', 'r', 'l', 'd']],
                'default'
            )
        );
    }

    public function test_get_by_path()
    {
        $input = [
            'general' => [
                'engine' => [
                    'name' => 'my-engine',
                    'stats' => [1, -2, 5]
                ],
            ],
            'date' => [
                'i18n' => [
                    'days' => [
                        'short' => ['Mon', 'Tue']
                    ]
                ]
            ]
        ];

        $this->assertEquals(
            'my-engine',
            Arr::get_by_path('general/engine/name', $input)
        );
        $this->assertEquals(
            'my-engine',
            Arr::get_by_path('/general/engine/name/', $input)
        );
        $this->assertEquals(
            [1, -2, 5],
            Arr::get_by_path('general/engine/stats', $input)
        );
        $this->assertEquals(
            -2,
            Arr::get_by_path('general/engine/stats/1', $input)
        );
        $this->assertEquals(
            'Mon',
            Arr::get_by_path('date/i18n/days/short/0', $input)
        );
        $this->assertEquals(
            [
                'days' => [
                    'short' => ['Mon', 'Tue']
                ]
            ],
            Arr::get_by_path('date/i18n', $input)
        );
        $this->assertEquals(
            'nooop',
            Arr::get_by_path('date/i18n/days/short/12', $input, 'nooop')
        );
    }
}
