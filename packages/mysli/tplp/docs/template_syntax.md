# Tplp: Template Syntax

## Variables

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

## Functions

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

## Build-In Functions

### abs

Returns the absolute value of number.

```
{-12|abs} // => 12
```

### ucfirst

Make a string's first character uppercase.

```
{'hello world!'|ucfirst} // => Hello world!
```

### ucwords

Uppercase the first character of each word in a string.

```
{'hello world!'|ucwords} // => Hello World!
```

### lower

Make a string lowercase.

```
{'Hello World!'|lower} // => hello world!
```

### upper

Make a string uppercase.

```
{'Hello World!'|upper} // => HELLO WORLD!
```

### date

Format a local time/date.

{[value](http://php.net/manual/en/function.strtotime.php)|date:[format](http://php.net/manual/en/function.date.php)}

```
{'now'|date:'d.m.y'} // => 02.04.14
{'tomorrow'|date:'d.m.y'} // => 03.04.14
// $user['created_on'] = '20130201154612';
{user[created_on]|date:'d.m.y'} // => 01.02.14
```

### join

Join array elements with a string.

```
// $list = ['banana', 'orange', 'kiwi'];
{list|join:','} // => banana,orange,kiwi
```

### split

Split a string by string.

```
// $list = 'banana,orange,kiwi';
{list|split:','} // => ['banana', 'orange', 'kiwi']
{list|split:',',2} // => ['banana', 'orange,kiwi']
```

### length

Get string length.

```
{'hello'|length} // => 5
```

### word_count

Counts the number of words inside string.

```
{'hello world!'|word_count} // => 2
```

### count

Count all elements in an array.

```
// $animals = ['cat', 'chicken', 'dog', 'cow'];
{animals|count} // => 4
```

### nl2br

Inserts HTML line breaks before all newlines in a string.

```
// $string = "Hello\nWorld!";
{string|nl2br} // => Hello<br/>World!
```

### number_format

Format a number with grouped thousands.

```
{12000|number_format} // => 12,000
{12000|number_format:2} // => 12,000.00
{12000|number_format:4, '.', ','} // => 12.000,0000
```

See [PHP number_format](http://php.net/manual/en/function.number-format.php) for more examples.

### replace

Returns a string produced according to the formatting string format.

{[format](http://php.net/manual/en/function.sprintf.php)|replace:variables}

```
{'The %s contains %d monkeys'|replace:'tree',12} // => The tree contains 12 monkeys.
```

### round

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

### floor

Round fractions down.

```
{4.3|floor}   // => 4
{9.999|floor} // => 9
{-3.14|floor} // => -4
```

### ceil

Round fractions up.

```
{4.3|ceil}    // => 5
{9.999|ceil}  // => 10
{-3.14|ceil}  // => -3
```

### strip_tags

Strip HTML and PHP tags from a string.

```
{'<p>Hello world!</p>'|strip_tags} // => Hello world!
```

### show_tags

Convert special characters to HTML entities.

```
{'<p>Hello world!</p>'|show_tags} // => &lt;p&gt;Hello world!&lt;/p&gt;
```

### trim

Strip whitespace (or other characters) from the beginning and end of a string.

```
{'    Hello world!      '|trim} // => Hello world!
```

### slice

Extract a slice of the array or return part of a string.

```
// $list = ['banana', 'orange', 'kiwi', 'apple', 'strawberry']
{list|slice:0,2} // => ['banana', 'orange']
{'hello world!'|slice:0,5} // => hello
```

### word_wrap

Wraps a string to a given number of characters.

```
{'The quick brown fox jumped over the lazy dog.'|word_wrap:20} // => The quick brown fox<br />jumped over the lazy<br />dog.
```

### max

Find highest value.

```
// $list = [1, 4, 54, 3, 450, 2];
{list|max} // => 450
{10|max:80,30,2} // => 80
```

### min

Find lowest value.

```
// $list = [1, 4, 54, 3, 450, 2];
{list|min} // => 1
{10|min:80,30,2} // => 2
```

### column

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

### reverse

Return an array with elements in reverse order or reverse a string.

```
// $list = [1, 2, 3, 4];
{list|reverse} // => [4, 3, 2, 1]
{'hello'|reverse} // => olleh
```

### contains

Checks if a value exists in an array or in string.

```
// $list = ['hello', 'world'];
{list|contains:'world'} // => true
{'hello world'|contains:'world'} // => true
{'hello'|contains:'world'} // => false
```

### key_exists

Checks if the given key or index exists in the array.

```
// $list = ['id' => 12, 'name' => 'Marko'];
{list|key_exists:'id'} // => true
{list|key_exists:'na'} // => false
```

### sum

Calculate the sum of values in an array.

```
// $list = [10, 20, 5];
{list|sum} // => 35
```

### unique

Removes duplicate values from an array.

```
// $list = ['one', 'two', 'one', 'three', 'one'];
{list|unique} // => ['one', 'two', 'three']
```

### range

Create an array containing a range of elements.

```
{|range:0,6} // => [0, 1, 2, 3, 4, 5, 6]
```

### random

Generate a random integer.

```
{|rand:0,5} // => 0|1|2|3|4|5
```

## Control Structures

Use double colon (::) to start or end statement.

### If-elif-else

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

### For

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

### Functions

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

## Inclusions

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

## Translations

To use translations you need to set translator (with `set_translator`), then in
the template, translations are used the same as variables, only prefixed with at (@) symbol.

```
{@HELLO}
```

### Pluralization

```
{@COMMENTS(12)}

// With variable:
{@COMMENTS(comments_count)}

// With variable and function(s):
{@COMMENTS(comments|count)}
```

### Variables

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

## Comments

Use curly brackets with asterisk for comments:

```
{* I'm a comments! *}
```

## Escaping Characters and Regions

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
