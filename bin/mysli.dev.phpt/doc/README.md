# Mysli Dev PHPT

## Introduction

Wrapper for PHPT. This is a command line utility to add and execute PHPT files.
Majority of basic PHPT syntax is supported. Classes are partly based on original
run-tests.php script, by The PHP Group. Some functionality was removed (like
auto submitting the failed tests).

## Basic Usage

After you enable it, the package will be available through command line
interface. Use `./dot phpt` to execute it.

## Writing tests

### File System Structure

Create new directory called `tests` in the root folder of your package. Create
a new directory for each class you want to test. Create new `.phpt` file for
each method you want to test. A good practice is to append `_basic`,
`_variation` and `_error` the the filename to distinct the test type.
For example, if you're testing method `line` for class `output`, you'd create
tests files:

    output/line_basic.phpt
    output/line_variation.phpt
    output/line_error.phpt

To have more that one of a type, you'd append number, e.g.:

    output/line_basic2.phpt
    output/line_basic3.phpt

Those are suggested naming conventions. You're not obliged to follow them, the
only real requirement for tests to be run is, to have a `.phpt` extension and
that they're in `tests` folder of your package's root.

### Test File Syntax

All sections described on [PHP's QA Page](http://qa.php.net/phpt_details.php)
are valid, however only those are implemented:

    --TEST--
    --SKIPIF--
    --POST--, --PUT--, --POST_RAW--, --GZIP_POST--, --DEFLATE_POST--, --GET--
    --COOKIE--
    --INI--
    --ARGS--
    --ENV--
    --FILE--, --FILEEOF--, --FILE_EXTERNAL--
    --EXPECT--, --EXPECTF--, --EXPECTREGEX--, --EXPECT_EXTERNAL--, --EXPECTF_EXTERNAL--, --EXPECTREGEX_EXTERNAL--

Variables in `--ENV--` are not supported.

Future plans:

    --DESCRIPTION--
    --CREDITS--
    --HEADERS--
    --EXPECTHEADERS--

Mysli PHPT parse will automatically add namespace and include core package,
hence enable autoloading and `__use` function. Tests which will utilize this
functionality, will not run in native PHPT.

The list bellow is mostly copied directly from
[PHP's QA Page](http://qa.php.net/phpt_details.php), please visit the URL to
see more complete documentation with more examples. Note, that not all the
features described there are valid in Mysli implementation of PHPT.

#### --TEST--

Title of test as a single line short description. This section is required.

#### --SKIPIF--

A condition or set of conditions used to determine if a test should be skipped.

    --SKIPIF--
    <?php if (!true) die('Explanation...'); ?>

#### --POST--

POST variables or data to be passed to the test script.

    --POST--
    name=Inna&age=6

#### --POST_RAW--

Raw POST data to be passed to the test script. This differs from the section
above because it doesn't automatically set the Content-Type,
this leaves you free to define your own within the section.

    --POST_RAW--
    Content-type: multipart/form-data, boundary=AaB03x

    --AaB03x
    content-disposition: form-data; name="field1"

    Joe Blow
    --AaB03x
    content-disposition: form-data; name="pics"; filename="file1.txt"
    Content-Type: text/plain

    abcdef123456789
    --AaB03x--

#### --PUT--

Similar to the section above, PUT data to be passed to the test script.

    --PUT--
    Content-Type: text/json

    {"name":"default output handler","type":0,"flags":112,"level":0,"chunk_size":0,"buffer_size":16384,"buffer_used":3}

#### --GZIP_POST--

When this section exists, the POST data will be gzencode()'d.

--DEFLATE_POST--

When this section exists, the POST data will be gzencode()'d.

#### --GET--

GET variables to be passed to the test script.

    --GET--
    name=inna&age=6

#### --COOKIE--

Cookies to be passed to the test script.

    --COOKIE--
    hello=World;goodbye=MrChips

#### --INI--

To be used if you need a specific php.ini setting for the test.

    --INI--
    session.use_cookies=0
    session.cache_limiter=
    register_globals=1
    session.serialize_handler=php
    session.save_handler=files

#### --ARGS--

A single line of text that is passed as the argument(s) to the PHP CLI.

    --ARGS--
    --arg value --arg=value -avalue -a=value -a value

#### --ENV--

Configures environment variables such as those found in the
$_SERVER global array.

    --ENV--
    REDIRECT_URL=$scriptname
    PATH_TRANSLATED=c:\apache\1.3.27\htdocs\nothing.php
    QUERY_STRING=$filename
    PATH_INFO=/nothing.php
    SCRIPT_NAME=/phpexe/php.exe/nothing.php
    SCRIPT_FILENAME=c:\apache\1.3.27\htdocs\nothing.php

#### --FILE--

The test source code.

    --FILE--
    <?php echo "Hello world!"; ?>

#### --FILEEOF--

An alternative to --FILE-- where any trailing line breaks (\n || \r || \r\n
found at the end of the section) are omitted.

    --FILEEOF--
    <?php
    eval("echo 'Hello'; // comment");
    echo " World";
    //last line comment

#### --FILE_EXTERNAL--

n alternative to --FILE--. This is used to specify that an external file should
be used as the --FILE-- contents of the test file, and is designed for running
the same test file with different ini, environment, post/get or other external
inputs. Basically it allows you to DRY up some of your tests. The file must be
in the same directory as the test file, or in a subdirectory.

    --FILE_EXTERNAL--
    files/file012.php

#### --EXPECT--

The expected output from the test script. This must match the actual output
from the test script exactly for the test to pass.

    --EXPECT--
    array(2) {
      ["hello"]=>
      string(5) "World"
      ["goodbye"]=>
      string(7) "MrChips"
    }

#### --EXPECT_EXTERNAL--

Similar to to --EXPECT-- section, but just stating a filename where to load the
expected output from.

#### --EXPECTF--

An alternative of --EXPECT--. Where it differs from --EXPECT-- is that it uses
a number of substitution tags for strings, spaces, digits, etc. that appear in
test case output but which may vary between test runs. The most common example
of this is to use %s and %d to match the file path and line number which are
output by PHP Warnings.

The following is a list of all tags and what they are used to represent:

    %e: Represents a directory separator, for example / on Linux.
    %s: One or more of anything (character or white space) except the end of line character.
    %S: Zero or more of anything (character or white space) except the end of line character.
    %a: One or more of anything (character or white space) including the end of line character.
    %A: Zero or more of anything (character or white space) including the end of line character.
    %w: Zero or more white space characters.
    %i: A signed integer value, for example +3142, -3142.
    %d: An unsigned integer value, for example 123456.
    %x: One or more hexadecimal character. That is, characters in the range 0-9, a-f, A-F.
    %f: A floating point number, for example: 3.142, -3.142, 3.142E-10, 3.142e+10.
    %c: A single character of any sort (.).
    %r...%r: Any string (...) enclosed between two %r will be treated as a regular expression.
    %unicode|string%: Matches the string 'unicode' in PHP6 test output and 'string' in PHP5 test output.
    %binary_string_optional%: Matches 'Binary string' in PHP6 output, 'string' in PHP5 output. Used in PHP Warning messages.
    %unicode_string_optional%: Matches 'Unicode string' in PHP6 output, 'string' in PHP5 output. Used in PHP Warning messages.
    %u|b%: Matches a single 'u' in PHP6 test output where the PHP5 output from the same test hs no character in that position.

#### --EXPECTF_EXTERNAL--

Similar to to --EXPECTF-- section, but like the --EXPECT_EXTERNAL-- section
just stating a filename where to load the expected output from.

#### --EXPECTREGEX--

An alternative of --EXPECT--. This form allows the tester to specify the result
in a regular expression.

    --EXPECTREGEX--
    M_E       : 2.718281[0-9]*
    M_LOG2E   : 1.442695[0-9]*
    M_LOG10E  : 0.434294[0-9]*
    M_LN2     : 0.693147[0-9]*
    M_LN10    : 2.302585[0-9]*
    M_PI      : 3.141592[0-9]*
    M_PI_2    : 1.570796[0-9]*
    M_PI_4    : 0.785398[0-9]*
    M_1_PI    : 0.318309[0-9]*
    M_2_PI    : 0.636619[0-9]*
    M_SQRTPI  : 1.772453[0-9]*
    M_2_SQRTPI: 1.128379[0-9]*
    M_LNPI    : 1.144729[0-9]*
    M_EULER   : 0.577215[0-9]*
    M_SQRT2   : 1.414213[0-9]*
    M_SQRT1_2 : 0.707106[0-9]*
    M_SQRT3   : 1.732050[0-9]*

#### --EXPECTREGEX_EXTERNAL--

Similar to to --EXPECTREGEX-- section, but like the --EXPECT_EXTERNAL-- section
just stating a filename where to load the expected output from.

## License

The Mysli Dev PHPT is licensed under the GPL-3.0 or later.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
