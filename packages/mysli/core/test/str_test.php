<?php

namespace Mysli\Core\Lib;

// Exceptions, etc..
include(__DIR__.'/../core.php');
$core = new \Mysli\Core(__DIR__.'/dummy', __DIR__.'/dummy');

class StrTest extends \PHPUnit_Framework_TestCase
{
    // LIMIT REPEAT ------------------------------------------------------------

    public function test_limit_repeat()
    {
        $this->assertEquals(
            'Hello world!',
            \Core\Str::limit_repeat('Hello world!!!', '!', 1)
        );
    }
    public function test_limit_repeat_two()
    {
        $this->assertEquals(
            'Hello world!!',
            \Core\Str::limit_repeat('Hello world!!!!!!!!!', '!', 2)
        );
    }
    public function test_limit_repeat_array()
    {
        $this->assertEquals(
            ' Hello! World! ',
            \Core\Str::limit_repeat('   Hello!!!   World!!!   ', ['!', ' '], 1)
        );
    }

    // RANDOM ------------------------------------------------------------------

    public function test_random_alpha_lower()
    {
        $one = \Core\Str::random(10, 'a');
        $two = \Core\Str::random(10, 'a');

        $this->assertRegExp(
            '/^[a-z]*$/',
            $one
        );
        $this->assertRegExp(
            '/^[a-z]*$/',
            $two
        );
        $this->assertNotEquals($one, $two);
    }
    public function test_random_alpha_upper()
    {
        $one = \Core\Str::random(10, 'A');
        $two = \Core\Str::random(10, 'A');

        $this->assertRegExp(
            '/^[A-Z]*$/',
            $one
        );
        $this->assertRegExp(
            '/^[A-Z]*$/',
            $two
        );
        $this->assertNotEquals($one, $two);
    }
    public function test_random_numeric()
    {
        $one = \Core\Str::random(10, '1');
        $two = \Core\Str::random(10, '1');

        $this->assertRegExp(
            '/^[0-9]*$/',
            $one
        );
        $this->assertRegExp(
            '/^[0-9]*$/',
            $two
        );
        $this->assertNotEquals($one, $two);
    }
    public function test_random_special()
    {
        $one = \Core\Str::random(10, 's');
        $two = \Core\Str::random(10, 's');

        $this->assertRegExp(
            '/^[\~\#\$\%\&\(\)\=\?\*\<\>\-_:\.;,\+\!]*$/',
            $one
        );
        $this->assertRegExp(
            '/^[\~\#\$\%\&\(\)\=\?\*\<\>\-_:\.;,\+\!]*$/',
            $two
        );
        $this->assertNotEquals($one, $two);
    }
    public function test_random_alphanum()
    {
        $one = \Core\Str::random(10, 'aA1');
        $two = \Core\Str::random(10, 'aA1');

        $this->assertRegExp(
            '/^[a-z0-9]*$/i',
            $one
        );
        $this->assertRegExp(
            '/^[a-z0-9]*$/i',
            $two
        );
        $this->assertNotEquals($one, $two);
    }

    // STANDARDIZE LINE ENDINGS ------------------------------------------------

    public function test_unix_line_endings()
    {
        $this->assertEquals(
            "1\n2\n3\n",
            \Core\Str::to_unix_line_endings("1\r\n2\n3\r")
        );
    }
    public function test_unix_line_endings_limit()
    {
        $this->assertEquals(
            "1\n\n2\n\n3\n\n",
            \Core\Str::to_unix_line_endings("1\r\n\r\n2\n\n\n\n3\r\r\r\r\r", true)
        );
    }

    // NORMALIZE ---------------------------------------------------------------

    public function test_normalize()
    {
        $this->assertEquals(
            'V kozuscku hudobnega fanta stopiclja mizar.',
            \Core\Str::normalize('V kožuščku hudobnega fanta stopiclja mizar.')
        );
    }

    public function test_normalize_detect_encoding()
    {
        $this->assertEquals(
            'V kozuscku hudobnega fanta stopiclja mizar.',
            \Core\Str::normalize('V kožuščku hudobnega fanta stopiclja mizar.', null)
        );
    }

    // CLEAN -------------------------------------------------------------------

    public function test_clean_alpha_lower()
    {
        $this->assertEquals(
            'helloworld',
            \Core\Str::clean('hello world!', 'a')
        );
    }
    public function test_clean_alpha_lower_upper()
    {
        $this->assertEquals(
            'HelloWorld',
            \Core\Str::clean('Hello World!', 'aA')
        );
    }
    public function test_clean_alpha_num()
    {
        $this->assertEquals(
            'HelloWorld42',
            \Core\Str::clean('Hello World! 42..', 'aA1')
        );
    }
    public function test_clean_alpha_lower_num_space()
    {
        $this->assertEquals(
            'ello orld 42',
            \Core\Str::clean('Hello World! 42..', 'a1s')
        );
    }
    public function test_clean_all_costum()
    {
        $this->assertEquals(
            'Hello World! 42..',
            \Core\Str::clean('Hello World! 42..', 'aA1s', '!.')
        );
    }
    public function test_clean_all_costum_limit()
    {
        $this->assertEquals(
            'Hello World!',
            \Core\Str::clean('Hello World! 42..', 'aA1s', '!.', 12)
        );
    }

    // CLEAN REGEX -------------------------------------------------------------

    public function test_clean_regex()
    {
        $this->assertEquals(
            'elloworld4',
            \Core\Str::clean_regex('Hello world!! 42', '/[\ \!H0-3]/')
        );
    }

    // SLUG --------------------------------------------------------------------

    public function test_slug()
    {
        $this->assertEquals(
            'v-kozuscku-hudobnega-fanta-stopiclja-mizar',
            \Core\Str::slug('V kožuščku hudobnega fanta stopiclja mizar.')
        );
    }
    public function test_slug_multiple_spaces()
    {
        $this->assertEquals(
            'v-kozuscku-hudobnega-fanta-stopiclja-mizar',
            \Core\Str::slug('    ?  V koŽuščku     HUDOBNEGA  / --
                fanta     stopiclja mizar!!       .')
        );
    }

    // SLUG UNIQUE -------------------------------------------------------------

    public function test_slug_unique()
    {
        $this->assertEquals(
            'hello-world-2',
            \Core\Str::slug_unique('Hello World!', ['hello-world'])
        );
    }
    public function test_slug_unique_taken()
    {
        $this->assertEquals(
            'hello-world-3',
            \Core\Str::slug_unique('Hello World!', ['hello-world', 'hello-world-2'])
        );
    }
    public function test_slug_unique_taken_double()
    {
        $this->assertEquals(
            'hello-world-2-2',
            \Core\Str::slug_unique('Hello World 2', ['hello-world-2'])
        );
    }

    // LIMIT WORDS -------------------------------------------------------------

    public function test_limit_words()
    {
        $this->assertEquals(
            'The quick brown fox',
            \Core\Str::limit_words('The quick brown fox jumps over the lazy dog', 4)
        );
    }
    public function test_limit_words_ending()
    {
        $this->assertEquals(
            'The quick brown fox...',
            \Core\Str::limit_words(
                'The quick brown fox jumps over the lazy dog',
                4,
                '...'
            )
        );
    }

    // LIMIT LENGTH ------------------------------------------------------------

    public function test_limit_length()
    {
        $this->assertEquals(
            'The quick brown',
            \Core\Str::limit_length('The quick brown fox jumps over the lazy dog', 15)
        );
    }
    public function test_limit_length_ending()
    {
        $this->assertEquals(
            'The quick brown...',
            \Core\Str::limit_length(
                'The quick brown fox jumps over the lazy dog',
                15,
                '...'
            )
        );
    }

    // EXPLODE TRIM ------------------------------------------------------------

    public function test_explode_trim()
    {
        $this->assertEquals(
            ['one', 'two', 'three', 'four'],
            \Core\Str::explode_trim('::', 'one :: two :: three :: four')
        );
    }
    public function test_explode_trim_array_limit()
    {
        $this->assertEquals(
            ['one', 'two', 'three :: four'],
            \Core\Str::explode_trim(
                ['::', '!!'],
                'one :: two !! three :: four',
                null,
                3
            )
        );
    }

    // EXPLODE GET -------------------------------------------------------------

    public function test_explode_get()
    {
        $this->assertEquals(
            'three',
            \Core\Str::explode_get('::', 'one :: two :: three :: four', 2)
        );
    }

    // TOKENIZE ----------------------------------------------------------------

    public function test_tokenize()
    {
        $this->assertEquals(
            [
                "id='head'",
                "class='odd new'",
                "title='it's a nice day!'"
            ],
            \Core\Str::tokenize(
                "id='head' class='odd new' title='it\'s a nice day!'",
                ' ',
                '\''
            )
        );
    }
    public function test_tokenize_array()
    {
        $this->assertEquals(
            [
                "id(head)",
                "class(odd new)",
                "title(it's (a) nice day!)"
            ],
            \Core\Str::tokenize(
                "id(head) class(odd new) title(it's \(a\) nice day!)",
                ' ',
                ['(', ')']
            )
        );
    }

    // CENSOR ------------------------------------------------------------------

    public function test_censor()
    {
        $this->assertEquals(
            'Hi *****!',
            \Core\Str::censor('Hi peach!', 'peach', '*', 0)
        );
    }
    public function test_censor_double()
    {
        $this->assertEquals(
            'Hi +*+*+*+*+*!',
            \Core\Str::censor('Hi peach!', 'peach', '+*', 0)
        );
    }
    public function test_censor_keep_start()
    {
        $this->assertEquals(
            'Hi pe***!',
            \Core\Str::censor('Hi peach!', 'peach', '*', 2)
        );
        $this->assertEquals(
            'Hi pea**!',
            \Core\Str::censor('Hi peach!', 'peach', '*', '3-0')
        );
    }
    public function test_censor_keep_both()
    {
        $this->assertEquals(
            'Hi pe*ch!',
            \Core\Str::censor('Hi peach!', 'peach', '*', '2-2')
        );
    }
    public function test_censor_keep_end()
    {
        $this->assertEquals(
            'Hi ***ch!',
            \Core\Str::censor('Hi peach!', 'peach', '*', '0-2')
        );
    }
    public function test_censor_keep_array()
    {
        $this->assertEquals(
            'Hi pe***, banana, ap***!',
            \Core\Str::censor(
                'Hi peach, banana, apple!',
                ['peach', 'apple'],
                '*',
                2
            )
        );
    }
    public function test_censor_keep_aarray()
    {
        $this->assertEquals(
            'Hi fruit, ba****, fruit!',
            \Core\Str::censor(
                'Hi peach, banana, apple!',
                [
                    'peach'  => 'fruit',
                    'apple'  => 'fruit',
                    'banana' => null
                ],
                '*',
                2
            )
        );
    }

    // TO CAMELCASE ------------------------------------------------------------

    public function test_to_camelcase()
    {
        $this->assertEquals(
            'TheQuickBrownFox',
            \Core\Str::to_camelcase('the_quick_brown_fox')
        );
    }
    public function test_to_camelcase_lcfirst()
    {
        $this->assertEquals(
            'theQuickBrownFox',
            \Core\Str::to_camelcase('The_quick_brown_fox', false)
        );
    }
    public function test_to_camelcase_slash()
    {
        $this->assertEquals(
            'TheQuick/BrownFox/JumpsOver/TheLazyDog',
            \Core\Str::to_camelcase('the_quick/brown_fox/jumps_over/the_lazy_dog')
        );
    }
    public function test_to_camelcase_slash_lcfirst()
    {
        $this->assertEquals(
            'theQuick/brownFox/jumpsOver/theLazyDog',
            \Core\Str::to_camelcase(
                'the_quick/brown_fox/jumps_over/the_lazy_dog',
                false
            )
        );
    }
    public function test_to_camelcase_bslash()
    {
        $this->assertEquals(
            'TheQuick\\BrownFox',
            \Core\Str::to_camelcase('the_quick\\brown_fox')
        );
    }

    // TO UNDERSCORE -----------------------------------------------------------

    public function test_to_underscore()
    {
        $this->assertEquals(
            'the_quick_brown_fox',
            \Core\Str::to_underscore('TheQuickBrownFox')
        );
    }
    public function test_to_underscore_slash()
    {
        $this->assertEquals(
            'the_quick_brown_fox/jumps_over/the_lazy_dog',
            \Core\Str::to_underscore('TheQuickBrownFox/JumpsOver/TheLazyDog')
        );
    }
    public function test_to_underscore_bslash()
    {
        $this->assertEquals(
            'the_quick_brown_fox\\jumps_over\\the_lazy_dog',
            \Core\Str::to_underscore('TheQuickBrownFox\\JumpsOver\\TheLazyDog')
        );
    }
}
