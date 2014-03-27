<?php

namespace Mysli;

include(__DIR__.'/../i18n.php');
include(__DIR__.'/../parser.php');
include(__DIR__.'/../translator.php');
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);

class DummyConfig { public function get() { return; } }

class I18nTest extends \PHPUnit_Framework_TestCase
{
    protected $i18n;
    protected $translator;

    public function __construct()
    {
        $this->i18n = new \Mysli\I18n(['test/package', null], new DummyConfig());

        // Always create fresh cache
        $this->i18n->cache_create();
        $this->translator = $this->i18n->translator();
        $this->translator->primary('en');
    }

    public function test_instance()
    {
        $this->assertInstanceOf('\\Mysli\\I18n', $this->i18n);
    }

    public function test_translate()
    {
        $this->assertEquals(
            'Hello World!',
            $this->translator->translate('hello_world')
        );
    }

    public function test_translate_variable()
    {
        $this->assertEquals(
            'Hi there, stranger!',
            $this->translator->translate('greeting', 'stranger')
        );
        $this->assertEquals(
            'Hi there, stranger you\'re 23 years old.',
            $this->translator->translate('greeting_and_age', ['stranger', 23])
        );
    }

    public function test_translate_variable_advanced()
    {
        $this->assertEquals(
            'Hi there, please <a href="#li">login</a> or <a href="#re">register</a>.',
            $this->translator->translate(
                'greeting_and_register',
                [
                    '<a href="#li">%s</a>',
                    '<a href="#re">%s</a>',
                ]
            )
        );
    }

    public function test_translate_pluralization()
    {
        $this->assertEquals(
            'Comments',
            $this->translator->translate('comments')
        );
        $this->assertEquals(
            'No comments.',
            $this->translator->translate(['comments', 0])
        );
        $this->assertEquals(
            'One comment.',
            $this->translator->translate(['comments', 1])
        );
    }

    public function test_translate_pluralization_multi()
    {
        $this->assertEquals('Two or nine!', $this->translator->translate(['TWO_AND_NINE', 2]));
        $this->assertEquals('Two or nine!', $this->translator->translate(['TWO_AND_NINE', 9]));
        $this->assertNull($this->translator->translate(['TWO_AND_NINE', 0]));
        $this->assertNull($this->translator->translate(['TWO_AND_NINE', 1]));
        $this->assertNull($this->translator->translate(['TWO_AND_NINE', 3]));
        $this->assertNull($this->translator->translate(['TWO_AND_NINE', 8]));
        $this->assertNull($this->translator->translate(['TWO_AND_NINE', 19]));
        $this->assertNull($this->translator->translate(['TWO_AND_NINE', 20]));
    }

    public function test_translate_pluralization_and_variable()
    {
        $this->assertEquals(
            '2 comments.',
            $this->translator->translate(['comments', 2])
        );
        $this->assertEquals(
            '23 comments.',
            $this->translator->translate(['comments', 23])
        );
    }

    public function test_translate_pluralization_regex()
    {
        // Ending with 7
        $this->assertEquals('I\'m ending with 7!', $this->translator->translate(['numbers', 7]));
        $this->assertEquals('I\'m ending with 7!', $this->translator->translate(['numbers', 17]));
        $this->assertEquals('I\'m ending with 7!', $this->translator->translate(['numbers', 107]));
        $this->assertEquals('I\'m ending with 7!', $this->translator->translate(['numbers', -27]));

        $this->assertNull($this->translator->translate(['numbers', 72]));
        $this->assertNull($this->translator->translate(['numbers', 278]));

        // Start with 4
        $this->assertEquals('I\'m starting with 4!', $this->translator->translate(['numbers', 4]));
        $this->assertEquals('I\'m starting with 4!', $this->translator->translate(['numbers', 40]));
        $this->assertEquals('I\'m starting with 4!', $this->translator->translate(['numbers', 403]));
        $this->assertEquals('I\'m starting with 4!', $this->translator->translate(['numbers', -45]));

        $this->assertNull($this->translator->translate(['numbers', 24]));
        $this->assertNull($this->translator->translate(['numbers', 248]));

        // Start with one, end with two
        $this->assertEquals('I\'m starting with 1 and ending with 2!', $this->translator->translate(['numbers', 12]));
        $this->assertEquals('I\'m starting with 1 and ending with 2!', $this->translator->translate(['numbers', 132]));
        $this->assertEquals('I\'m starting with 1 and ending with 2!', $this->translator->translate(['numbers', 12434232]));
        $this->assertEquals('I\'m starting with 1 and ending with 2!', $this->translator->translate(['numbers', -1342]));
    }

    public function test_translate_pluralization_regex_multi()
    {
        $this->assertEquals('I\'m odd! :S', $this->translator->translate(['odd', 1]));
        $this->assertEquals('I\'m odd! :S', $this->translator->translate(['odd', 3]));
        $this->assertEquals('I\'m odd! :S', $this->translator->translate(['odd', 33]));
        $this->assertEquals('I\'m odd! :S', $this->translator->translate(['odd', 34959]));

        $this->assertNull($this->translator->translate(['odd', 2]));
        $this->assertNull($this->translator->translate(['odd', 34958]));
    }

    public function test_translate_pluralization_ranges()
    {
        $this->assertEquals('Hopes',      $this->translator->translate(['age', 0]));
        $this->assertEquals('Hopes',      $this->translator->translate(['age', 1]));
        $this->assertEquals('Will',       $this->translator->translate(['age', 2]));
        $this->assertEquals('Will',       $this->translator->translate(['age', 3]));
        $this->assertEquals('Purpose',    $this->translator->translate(['age', 4]));
        $this->assertEquals('Competence', $this->translator->translate(['age', 8]));
        $this->assertEquals('Fidelity',   $this->translator->translate(['age', 18]));
        $this->assertEquals('Love',       $this->translator->translate(['age', 25]));
        $this->assertEquals('Care',       $this->translator->translate(['age', 55]));
        $this->assertEquals('Wisdom',     $this->translator->translate(['age', 98]));
    }

    public function test_translate_multiline()
    {
        $this->assertEquals(
            'Hello, I\'m multi-line text, I\'ll be converted to one line.',
            $this->translator->translate('MULTILINE')
        );
    }

    public function test_translate_multiline_keep_lines()
    {
        $this->assertEquals(
            "Hello,\nthe text will stay\nin multiple lines!",
            $this->translator->translate('MULTILINE_KEEP_LINES')
        );
    }
}
