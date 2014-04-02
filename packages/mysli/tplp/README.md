# Tplp (Template Parser)

## Introduction

Tplp (Template Parser) package is a simple template parser.
It's minimal on resources - run only once, for each package you enable.
All templates are parsed and saved as a regular PHP.

##  Usage

In your package root create a new directory with name `templates` which will be
containing template files.

The template files needs to have `.tplm.html` extension.
You should save them as `UTF-8`, `no BOM`, with `LF` line endings.

Example of such directory structure:

```
vendor/
    package/
        templates/
            layout.tplm.html
            sidebar.tplm.html
            list.tplm.html
```

### Setup

When your package is enabled you can use method `cache_create`, which will parse
all files in `templates` folder of your package, and saved them as a regular PHP.
When your package is disabled, use method `cache_remove`, to remove previously
created PHP files.

Examples:

In your setup.php:

```php
public function before_enable()
{
    $this->tplp->create_cache();
    // If you've put your .tplm.html files to different director than _templates_,
    // then you can specify it when creating cache:
    // $this->tplp->create_cache('different_templates_directory')
}
public function before_disable()
{
    $this->tplp->remove_cache();
}
```

### General

You package will receive `tplp` object, which can be used to create cache (as described above),
or to access individual templates of your package (`$tplp->template('my_template')`).

```php
public function __construct($tplp, $i18n)
{
    $template = $tplp->template('home_page');

    // Add template's variable
    $template->variable_add('username', 'Anonymous');

    // Add template's function
    $template->function_add('my_func', function ($param) {
        return $param;
    });

    // Set translator (if you use it)
    $template->translator_set($i18n->translator());

    // Process PHP with variables and return HTML
    $template->render(); // => HTML
}
```

### Functions and Variables

Individual templates are separate objects. You can set variables and functions,
which are local to just one template - for example _user_edit_ can have different
functions and variables than _user_create_.

You can register functions and variables on three levels: `global`, `package` and
`template`.

#### Global

Globally registered functions and variables will be passed to all packages.
To register function globally use `$tplp->function_register($name, $callable)`.
To unregister global function use: `$tplp->function_unregister($name)`. You can
use `variable_register` and `variable_unregister` for variables. Example:

```php
// Package 1
$tplp->variable_register('title', 'My Package #1');

// Package 2
// Variable is available in second package
$tplp->variable_get('title'); // => 'My Package #1';

$template = $tplp->template('index');
// Variable is available in individual template of second package.
$template->variable_get('title'); // => 'My Package #1';
// You can rewrite variable at any level...
$template->variable_set('title', 'Different Title!');
```

#### Package

You can use `$tplp->function_set($name, $callable)` and `$tplp->variable_set($name, $callable)`;
when you request individual template, all the functions and variables defined will be passed on.

The difference between _package_ and _global_ level is, that function/variables defined on
package level, will **not** be available in other packages.

Examples:

```php
// Package 1
$tplp->variable_set('title', 'My Package ::');
$login = $tplp->template('login');
$login->variable_get('title'); // => My Package ::
$register = $tplp->template('register');
$register->variable_get('title'); // => My Package ::

// Package 2
$tplp->variable_get('title'); // => null
```

#### Template

Finally, the template level; functions and variables defined here, will be available
only to this particular template.

Examples:

```php
$login = $tplp->template('login');
$login->variable_set('title', 'My Package :: Login');
$login->variable_get('title'); // => My Package :: Login

$register = $tplp->template('register');
$register->variable_get('title'); // => null
$register->variable_set('title', 'My Package :: Register');
$register->variable_get('title'); // => My Package :: Register
```

## Template Syntax

### Variables

Put variable name between curly brackets, do not use dollar symbol; variables
are automatically echoed:

```php
// PHP
$variable = 'world!';
$user['uname'] = 'Leonardo da Vinci';
$pet = new Cat();
$pet->name = 'Riki';
```

```
// Template
Hello, {variable}!
Your name is {user[uname]}.
Your pet name is {$pet->name}.
```

```
// Output
Hello, world!!
Your name is Leonardo da Vinci.
Your pet name is Riki.
```

### Functions

You can use functions in combination with variables; use pipe (|) symbol to
pass variable to function (it will always be passed as a first argument).

```
{name|lower} // => <?php echo strtolower($name); ?>
// LEONARDO DA VINCI => leonardo da vinci
```

You can chain more functions together:

```
{name|lower|ucfirst} // => <?php echo ucfirst(strtolower($name)); ?>
// LEONARDO DA VINCI => Leonardo Da Vinci
```

You can pass arguments to functions:

```
{name|slice:0,8} // => <?php echo substr($name, 0, 8); ?>
// Leonardo Da Vinci => Leonardo
```

Arguments can be variables:

```
{description|word_wrap:blog[line_width]} // => <?php echo wordwrap($description, $blog['line_width']); ?>
```

You can use functions with non variables also, types are accepted: numeric, boolean,
null, strings.

```
{24,im_function} => <?php echo im_function(24); ?>
{true,im_function} => <?php echo im_function(true); ?>
{null,im_function} => <?php echo im_function(null); ?>
{'Hello World!',im_function} => <?php echo im_function('Hello World!'); ?>
```

### Build-In Functions

#### abs

Returns the absolute value of number.

```
{-12|abs} // => 12
```

#### ucfirst

Make a string's first character uppercase.

```
{'hello world!'|ucfirst} // => Hello world!
```

#### ucwords

Uppercase the first character of each word in a string.

```
{'hello world!'|ucwords} // => Hello World!
```

#### lower

Make a string lowercase.

```
{'Hello World!'|lower} // => hello world!
```

#### upper

Make a string uppercase.

```
{'Hello World!'|upper} // => HELLO WORLD!
```

#### date

Format a local time/date.

{[value](http://php.net/manual/en/function.strtotime.php)|date:[format](http://php.net/manual/en/function.date.php)}

```
{'now'|date:'d.m.y'} // => 02.04.14
{'tomorrow'|date:'d.m.y'} // => 03.04.14
// $user['created_on'] = '20130201154612';
{user[created_on]|date:'d.m.y'} // => 01.02.14
```

#### join

Join array elements with a string.

```
// $list = ['banana', 'orange', 'kiwi'];
{list|join:','} // => banana,orange,kiwi
```

#### split

Split a string by string.

```
// $list = 'banana,orange,kiwi';
{list|split:','} // => ['banana', 'orange', 'kiwi']
{list|split:',',2} // => ['banana', 'orange,kiwi']
```

#### length

Get string length.

```
{'hello'|length} // => 5
```

#### word_count

Counts the number of words inside string.

```
{'hello world!'|word_count} // => 2
```

#### count

Count all elements in an array.

```
// $animals = ['cat', 'chicken', 'dog', 'cow'];
{animals|count} // => 4
```

#### nl2br

Inserts HTML line breaks before all newlines in a string.

```
// $string = "Hello\nWorld!";
{string|nl2br} // => Hello<br/>World!
```

#### number_format

Format a number with grouped thousands.

```
{12000|number_format} // => 12,000
{12000|number_format:2} // => 12,000.00
{12000|number_format:4, '.', ','} // => 12.000,0000
```

See [PHP number_format](http://php.net/manual/en/function.number-format.php) for more examples.

#### replace

Returns a string produced according to the formatting string format.

{[format](http://php.net/manual/en/function.sprintf.php)|replace:variables}

```
{'The %s contains %d monkeys'|replace:'tree',12} // => The tree contains 12 monkeys.
```

#### round

Rounds a float.

```
{3.4|round}         // => 3
{3.5|round}         // => 4
{3.6|round}         // => 4
{3.6|round:0}       // => 4
{1.95583|round:2}   // => 1.96
{1241757,|round:-3} // => 1242000
{5.045|round:2}     // => 5.05
{5.055|round:2}     // => 5.06
```

#### floor

Round fractions down.

```
{4.3|floor}   // => 4
{9.999|floor} // => 9
{-3.14|floor} // => -4
```

#### ceil

Round fractions up.

```
{4.3|ceil}    // => 5
{9.999|ceil}  // => 10
{-3.14|ceil}  // => -3
```

#### strip_tags

Strip HTML and PHP tags from a string.

```
{'<p>Hello world!</p>'|strip_tags} // => Hello world!
```

#### show_tags

Convert special characters to HTML entities.

```
{'<p>Hello world!</p>'|show_tags} // => &lt;p&gt;Hello world!&lt;/p&gt;
```

#### trim

Strip whitespace (or other characters) from the beginning and end of a string.

```
{'    Hello world!      '|trim} // => Hello world!
```

#### slice

Extract a slice of the array or return part of a string.

```
// $list = ['banana', 'orange', 'kiwi', 'apple', 'strawberry']
{list|slice:0,2} // => ['banana', 'orange']
{'hello world!'|slice:0,5} // => hello
```

#### word_wrap

Wraps a string to a given number of characters.

```
{'The quick brown fox jumped over the lazy dog.'|word_wrap:20} // => The quick brown fox<br />jumped over the lazy<br />dog.
```

#### max

Find highest value.

```
// $list = [1, 4, 54, 3, 450, 2];
{list|max} // => 450
{10|max:80,30,2} // => 80
```

#### min

Find lowest value.

```
// $list = [1, 4, 54, 3, 450, 2];
{list|min} // => 1
{10|min:80,30,2} // => 2
```

#### column

Return the values from a single column in the input array.

```
// $records = [
//     ['id' => 2135, 'first_name' => 'John', 'last_name' => 'Doe'   ],
//     ['id' => 3245, 'first_name' => 'Sally', 'last_name' => 'Smith'],
//     ['id' => 5342, 'first_name' => 'Jane', 'last_name' => 'Jones' ],
//     ['id' => 5623, 'first_name' => 'Peter', 'last_name' => 'Doe'  ]
// ];

{records|column:'first_name'} // => [0 => 'John', 1 => 'Sally', 2 => 'Jane', 3 => 'Peter']
{records|column:'first_name','id'} // => [2135 => 'John', 3245 => 'Sally', 5342 => 'Jane', 5623 => 'Peter']
```

#### reverse

Return an array with elements in reverse order or reverse a string.

```
// $list = [1, 2, 3, 4];
{list|reverse} // => [4, 3, 2, 1]
{'hello'|reverse} // => olleh
```

#### contains

Checks if a value exists in an array or in string.

```
// $list = ['hello', 'world'];
{list|contains:'world'} // => true
{'hello world'|contains:'world'} // => true
{'hello'|contains:'world'} // => false
```

#### key_exists

Checks if the given key or index exists in the array.

```
// $list = ['id' => 12, 'name' => 'Marko'];
{list|key_exists:'id'} // => true
{list|key_exists:'na'} // => false
```

#### sum

Calculate the sum of values in an array.

```
// $list = [10, 20, 5];
{list|sum} // => 35
```

#### unique

Removes duplicate values from an array.

```
// $list = ['one', 'two', 'one', 'three', 'one'];
{list|unique} // => ['one', 'two', 'three']
```


### Control Structures

Use double colon (::) to start or end statement.

#### If-elif-else

```
::if variable > 0
    Above zero.
::elif variable < 0
    Bellow zero.
::else
    Zero.
::/if
```

```php
<?php if ($variable > 0): ?>
    Above zero.
<?php elseif ($variable < 0): ?>
    Bellow zero.
<?php else: ?>
    Zero.
<?php endif; ?>
```

#### For

```
::for user in users
    Username: {user[uname]}<br/>
::/for
```

```php
<?php foreach ($users as $user): ?>
    Username: <?php echo $user['uname']; ?><br/>
<?php endforeach; ?>
```

```
::for uid,user in users
    User: {uid} => {user[uname]}<br/>
::/for
```

```php
<?php foreach ($users as $udi => $user): ?>
    Username: <?php echo $user['uname']; ?><br/>
<?php endforeach; ?>
```

#### Functions

In if-elif and for statements you can use functions:

```
::if users|count > 100
    More than 100 users!
::/if
```

```php
<?php if (count($users) > 100): ?>
    More than 100 users!
<?php endif; ?>
```

```
::for source in sources|split:'||'
    {source}
::/for
```

```php
<?php foreach (explode('||', $sources) as $source): ?>
    <?php echo $source; ?>
<?php endforeach; ?>
```

### Inclusions

You can include templates with `::use name`; extend template (use is as mater),
you can use following statement `::use name as master`.

```
::use sidebar
::use layout as master
```

File extensions must be omitted.

Templates are always included from root directories, so paths are never relative.

```
// Even if we're in assets folder, we still specify full absolute path...
::use assets/sidebar
```

Master template need to have `::yield` statement.

Examples:

File layout.tplm.html

```html
<!DOCTYPE html>
<html>
<head>
    <title>Hi, I'm master!</title>
</head>
<body>
    ::yield
</body>
</html>
```

File index.tplm.html

```html
::use layout as master
Hi - I'm index! :)
<div class="sidebar">
    ::use assets/sidebar
</div>
```

File assets/sidebar.tplm.html

```html
Hi, I'm sidebar!
::use assets/about
```

File assets/about.tplm.html

```html
A little bit about this blog.
```

Final result:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Hi, I'm master!</title>
</head>
<body>
Hi - I'm index! :)
<div class="sidebar">
    Hi, I'm sidebar!
    A little bit about this blog.
</div>
</body>
</html>
```

### Translations

To use translations you need to set translator (with `translator_set`), then in
the template, translations are used the same as variables, only prefixed with at (@) symbol.

```
{@HELLO}
```

#### Pluralization

```
{@COMMENTS(12)}

// With variable:
{@COMMENTS(comments_count)}

// With variable and function(s):
{@COMMENTS(comments|count)}
```

#### Variables

```
{@HELLO_USER username}
{@HELLO_USER user[uname]}
{@HELLO_USER user1, user2, false, 'string!'}

// Can be in multiple lines:
{@HELLO_USER
    user1,
    user2,
    false,
    'string!'}

// Combined with pluralization
{@HELLO_USER(users|count)
    user1,
    user2,
    false,
    'string!'}
```

### Comments

Use curly brackets with asterisk for comments:

```
{* I'm a comments! *}
```

### Escaping Characters and Regions

Use backslash to escape curly brackets or apostrophe: `\{, \}, \'`

**Important** all apostrophe which are not meant to declare a string
region should be escaped.

For parser to ignore particular region of your template, use three curly brackets:

```javascript
{{{
    // Nothing in here will be parsed...
    function hello (who) {
        return 'Hello ' + who;
    }
}}}
```

## Events

This package emits no events.

## Role

This package has a standard role `~tplp`.

You can make your own implementation of tplp package. You can extend the file
format, but make sure to fully support existing syntax.

Please see API section bellow for list of methods to be implemented.

## API

### \Mysli\Tplp

use \Mysli\Tplp\ExtData;

#### null __construct ( array $pkgm_trace )

This class will be automatically constructed for you package.

#### null function_register ( string $name, callable $function )

Add globally available function. This function will be available to all templates
and packages.

```php
$tplp->function_register('my_func', function ($param) {
    return $param;
});
```

#### null function_unregister ( string $name )

Remove globally available function.

#### null variable_register ( string $name, mixed $value )

Add globally available variable. This variable will be available to all templates
and packages.

```php
$tplp->variable_register('my_variable', 'Hello world!');
$login = $tplp->template('login');
$login->variable_get('my_variable'); // => Hello world!
```

#### null vsriable_unregister ( string $name )

Remove globally available variable.

#### null translator_set ( object $translator )

If you wish your templates to be translated, then you need to send translator
object to template(s).

```php
public function __construct($tplp, $i18n)
{
    $tplp->translator_set($i18n->translator());

    // OR You can set translator individually to each template also:

    $template = $tplp->template('home_page');

    // Set for this particular template
    $template->translator_set($i18n->translator());
}
```

#### object template( string $name )

Get template by name. Return `\Mysli\Tplp\Template` object.

```php
$template = $tplp->template('index');
$template->render(); // => HTML
```

#### integer cache_create ( string $folder = 'templates' )

Create cache for current package.

#### boolean cache_remove ( void )

Remove cache for current package.


### \Mysli\Tplp\Template

use \Mysli\Tplp\ExtData;

You don't need to construct this class directly, you can instead use `$tplp->template('template_name');`.

#### null __construct ( string $filename, object $translator = null, array $variables = [], array $functions = [] )

Construct template, require _filename_ which need to be full absolute path to
the template's PHP file.

#### null translator_set ( object $translator )

Assign translator object to the template. If you did this in main object, then
you don't need to do it here. Example:

```php
$tplp->translator_set($i18n->translator());
$template = $tplp->template('home_page');
$template->render(); // => HTML

// OR, The same... You can set translator individually to each template also:

$template = $tplp->template('home_page');

// Set for this particular template
$template->translator_set($i18n->translator());
$template->render(); // => HTML
```

#### string render ( void )

Process template, execute code with variables, and return result (HTML).

#### string php ( void )

Return template's unprocessed PHP code.


### \Mysli\Tplp\Parser

Please note, this class is used internally to parse template (string).

#### null __construct ( string $template )

Require string - actual template.

#### string parse ( void )

Return parsed template.

### \Mysli\Tplp\ExtData

#### null function_set ( string $name, callable $function )

Set a costume new function.

```php
$template->function_set('my_function', function ($param) {
    return $param;
});
```

If function is set on `tplp` object, then all templates (of package) will receive
that function.

**Warning!** Your functions shouldn't start with `tplp_`, those are reserved.

#### null variable_remove ( string $name )

Remove particular function.

#### null variable_set ( string|array $name, mixed $value = null )

You can set one or more variables used by template. Examples:

```php
$template->variable_set('var', 'value');
$template->variable_set([
    'var1' => 'val1',
    'var2' => 'val2',
    'var3' => 'val3'
]);
```

If variable is set on `tplp` object, then all templates (of package) will receive
that variable.

**Warning!** Your variables shouldn't start with `tplp_`, those are reserved.

#### null variable_remove ( string $name )

Remove particular variable.

#### mixed variable_get ( string $name )

Get variable's value if set, null otherwise.

### \Mysli\Tplp\InclusionsResolver

Please note, this class is used internally to resolve templates' inclusions.

#### null __construct ( array $templates )

Require an array, list of templates in format handle => string.

#### array resolve ( void )

Resolve templates' inclusions and return resolved list.
