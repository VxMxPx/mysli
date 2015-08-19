# Developer Test Advanced

## __init

In a root directory of any tests' class `__init.php` file can be placed.
This file will behave like any other `__init` file, if `__init` method is
define, it will be run once before tests for that particular class are
executed.

The good use of __init is to override a class you're about to test. Example:

    <?php
    // Namespace follows conventions
    namespace vendor\package\root\tests\class_to_test;

    class new_parser extends vendor\package\parser
    {
        static function save() { return true; }
        static function load() { return true; }
    }

    // Later in test

    #: Before
    use new_parser as parser;

    // ...
