# Test Syntax

Test is more or less a regular PHP file. The most basic example of test would be:

    #: Test Weather True is Really True
    #: Expect True
    return true;

First and second line contains a special directive (`#: `), following directives
are available:

## #: Test [DESCRIPTION]

Start a new test, everything following this directive, if not a new directive,
will be interpreted as a test body.

_TEST BODY_

Test body can be any PHP, think of it as a function's body.
There should always be a return value, except when an exception is thrown
or an output produced.

## #: Description DESCRIPTION

Longer description of a test. This directive is optional _and not implemented at the moment_.

## #: Expect OPTIONS

This directive can be omitted, when test returns results of an assertion,
for example:

    return assert::equals(12, 12);

Assertion class is useful when testing for complex structures, like arrays,
objects, etc... See the `assert` API for more details.

Expect is useful either for basic types, like integers, strings and booleans or
special cases like an exception and output.

Following _OPTIONS_ are supported:

**Assertion**

This is a default option and will be used if entire `expect` directive is missing.

**String This is expected string...**

String will be trimmed, if you'd like to test for presence of spaces wrap it
double quotes: `#: Expect String "  This is expected string  "`.

**Integer 12**

**Float 12.345**

**Match This is ***

Will match a very simple regular expression. Basically `*` is interested as
any character(s), so to match a string with a variable segment, asterisk
can be used, e.g.: `#: Expect Match The script needed * to execute.`

**Boolean True**

**Null**

**Instance vendor\package\class**

**Exception vendor\package\exception\class 12 Woops, something went wrong...**

When comparing an exception, code (12 in above example) and message are optional,
if not provided, they'll be ignored.

**Output <<<OUTPUT**

This will verify the output (e.g. echoed text). In this case, no return value
is expected, but in the body of your test, heredoc string is expected, e.g.
above example specified that heredoc will be called <<<OUTPUT, so it should
be defined in body in a following way:

    <<<OUTPUT
    Here's the expected outpu..
    OUTPUT;

## #: Skip [DESCRIPTION]

Skip the test if requirements are meet. Skip expecting a body on a next line,
and will be terminated by a new directive. Example:

    #: Test Foo
    #: Skip If True.
    return true; // This test will be skipped
    #: Expect Null
    return;

## #: Define NAME

Define a new re-usable block of code, which can be used in any test. Example:

    #: Define Data
    $data = [ ... ];

## #: Use NAME

Use a early defined block of code. Example:


    #: Define Data
    $data = [ ... ];

    #: Test Foo
    #: Use Data
    foo::process($data);

Multiple uses per test are allowed.

## #: Before

Code to be run before each test. Common usage for this is to import class which
is being tests, for example:

    #: Before
    use vendor\package\foo;

    #: Test Foo
    #: Expect True
    return foo::do_true();

## #: After

Code to be run after each test.
