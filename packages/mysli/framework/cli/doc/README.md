# Mysli Framework Cli

## Introduction

Command line interface and related utilities.

## Usage

Cli will be automatically run when you execute ./dot (which you can find in the
private path of your application). It will scan all enabled packages, for
available scripts.

If you want to create new scrip put it into the `src/` folder of your package,
it can be called anyway you want, namespace, must follow directory structure
convention, e.g. script in folder `vendor/package/src/script/my_script.php` must
contain class: `namespace vendor\package\script { class my_script {} }`.
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
output::format("Doday is a +bold%s-bold day!", ['nice']);
```

To open a tag, plus (+) is used, and to close you must use minus (-), e.g:
`+bold` text `-bold`, alternatively you can use `-all` to close all opened tags.

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
print output::red('Hello ' . output::bold('world!'));
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

After you've constructed it, you can add expected parameters:

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
    type       // type (str,bool,int,float)
    default    // default value
    help       // help text
    required   // weather field is required
    positional // weather this is positional parameter (auto set from $name)
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

... then check if process succeeded, if not, print _error_ messages, otherwise,
get values:

```php
if ($param->is_valid()) {
    echo $param->messages();
} else {
    $values = $param->values();
}
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
