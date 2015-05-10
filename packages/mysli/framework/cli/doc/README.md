# Mysli Framework Cli

## Introduction

Command line interface and related utilities.

## Usage

Cli will be automatically run when you execute ./dot (which you can find in the
private path of your application). It will scan all enabled packages, for
available scripts.

If you want to create new scrip put it into the `src/php/` folder of your package,
it can be called anyway you want, namespace, must follow directory structure
convention, e.g. script in folder `vendor/package/src/php/sh/my_script.php` must
contain class: `namespace vendor\package\sh { class my_script {} }`.
The class should contain static method `run` which will get passed arguments.

## Helper Classes

Cli package, offers couple of classes to help you manage input, output and
input parameters...

### Input

To include it use: `__use(__namespace__, 'mysli/framework/cli/input')`.

To grab single line input, you can use `line` method:

```php
$input = input::line('Enter you text: ');
```

... this method will accept second parameter, a function, which will run while
`null` is being returned:

```php
list($uname, $domain) = input::line('Enter an email: ', function ($input) {
        if (strpos($input, '@') && strpos($input, '.')) {
            return explode('@', $input, 2);
        } else {
            echo "Invalid e-mail address, try again.\n"
            return;
        }
    });
```

Additional to `line`, there are also `multiline` and `password`
methods available. They accept the same arguments, the difference being,
`multiline` will terminate on two new lines, and password will hide input.

Finally `confirm` is available which will print `y/n` to the user, and return
boolean value.

```php
// Second parameter (boolean) will set default value.
$answer = input::confirm('Are you sure?', false);
```

### Output

To include it use: `__use(__namespace__, 'mysli/framework/cli/output')`.

To output a single line of regular text, you can use:

```php
output::line('Hello world!');
```

To fill full width of terminal window with a particular character you can use
`fill` method:

```php
output::fill('-');
```

To format (change text color, background color,...) a string,
you can use `format`:

```php
output::format("Today is a +bold%s-bold day!", ['nice']);
```

To open a tag, plus (+) is used, and for closing it minus (-), e.g:
`+bold` text `-bold`, alternatively `-all` can be used,
to close all opened tags.

Available tags are:

Formating: bold, dim, underline, blink, invert, hidden

Text color: default, black, red, green, yellow, blue, magenta, cyan, light_gray,
dark_gray, light_red, light_green, light_yellow, light_blue, light_magenta,
light_cyan, white

Background color: bg_default, bg_black, bg_red, bg_green, bg_yellow, bg_blue,
bg_magenta, bg_cyan, bg_light_gray, bg_dark_gray, bg_light_red, bg_light_green,
bg_light_yellow, bg_light_blue, bg_light_magenta, bg_light_cyan, bg_white

There are shortcut methods available for each tag:

```php
output::red('Red text!');
output::green('Green text!');
```

... plus some additional options:

```php
output::plain('Text!');   // => plain (::line)
output::error('Text!');   // => red
output::warn('Text!');    // => yellow
output::success('Text!'); // => green
```

### Parameters

This class can be used to define parameters which you scripts accepts,
and then to parse arguments.

To include it use: `__use(__namespace__, 'mysli/framework/cli/param')`.

This class cannot be called statically, so you need to construct it with:

```php
$param = new param('My Script!', $arguments);
```

... the first parameter is _title_ which will be used when help is printed for
a script. The second parameter is optional, if not provided, `$_SERVER['argv']`
will be used.

Additional to that, you can also use (all used for help text):

```php
$param->title   = 'Title'; // same as title on consruct
$param->command = 'command'; // name of this script as called with ./dot script
$param->description = 'Short description'; // display right after title
$param->description_long = 'Long description'; // displayed on the bottom
```

To add expected parameters use `add` method:

    $param->add($name, $options);

The `$name` parameter can be either:

    -s         // short parameter
    -s/--long  // short and long
    --long     // only long
    POSITIONAL // positional parameter

The `$options` parameter is an array with following options:

    id         // unique id (position || long || short) (auto set from $name)
    short      // short (-s) (auto set from $name)
    long       // long (--long) (auto set from $name)
    type       // type (str,bool,int,float,arr)
    min        // minimum value for float or integer,
               // minimum number of arguments if type is arr
    max        // maximum value for float or integer,
               // maximum number of arguments if type is arr
    default    // default value
    help       // help text
    required   // weather field is required
    positional // weather this is positional parameter (auto set from $name)
    exclude    // which arguments cannot be set in combination with this one
               // array e.g. ['param_id_1', 'param_id_2']
    invoke     // if parameter is present a provided method will be executed
    action     // func to be executed when field is parsed:
               // value, is_valid, messages, break=false
    invert     // invert bool value
    ignore     // weather value should be ignored

... an valid example of boolean parameter would be:

```php
$param->add('-v/--verbose', [
    'type'    => 'bool',
    'default' => false,
    'help'    => 'explain what is being done']);
```

To process arguments, use `parse`:

```php
$param->parse();
```

... then check if process succeeded and get values, otherwise print error
messages:

```php
if ($param->is_valid()) {
    $values = $param->values();
} else {
    echo $param->messages();
}
```

Each parameter has an unique ID, which if not set, will be automatically
generated, in following way:

    -s/--long  => long
    -s         => s
    POSITIONAL => positional

#### Advanced options

To allow one or another parameter, but not both, you can use `exclude` option:

```php
$param->add('-p/--param1', ['exclude' => ['param2']]);
$param->add('--param2');
```

... in the above example `param1` and `param2` will exclude each other,
meaning that if both will be present, warning will be displayed and validation
will not pass.

To invoke a particular method, you can use `invoke`. The method will be invoked
after all arguments are parsed. It will be invoked **only** if validation
passes.

```php
$param->add('-m', ['invoke' => 'my_function']);
function my_function($value, $arguments) {
    // $value is value of -m
    // $arguments are all processed arguments (same as $param->values())
}
```

To execute a function when an argument is being parsed you can use `action`.

```php
$param->add('-m', ['action' => function (&$value, &$is_valid, &$messages) {
    if (file_exists($value)) {
        $value = file_get_contents($value);
    } else {
        $is_valid = false;
        $messages[] = "File not found: {$file}";
    }
}]);
```

### Util

To include it use: `__use(__namespace__, 'mysli/framework/cli/util')`.

Util class has only couple of methods: `terminal_width`, `execute` and
`fork_command` (will fork a command and return _pid_).

## License

The Mysli Framework Cli is licensed under the GPL-3.0 or later.

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
