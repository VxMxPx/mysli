# Tplp (Template Parser)

## Introduction

Tplp (Template Parser) package is a simple template parser.
It's minimal on resources - run only once, for each package you enable.
All templates are parsed and saved as a regular PHP.

##  Usage

In your package root create a new directory with name `templates` which will be
containing template files.

The template files needs to have `.tplm.html` extension.
You should save them as `UTF-8`, `no BOM`, with `LF` line endings, preferably to
`templates` folder in you package's root.

Example of such directory structure:

```
vendor/
    package/
        templates/
            layout.tplm.html
            sidebar.tplm.html
            list.tplm.html
```

When your package is enabled you can use method `cache_create`, which will parse
all files in `templates` folder of your package, and saved them as a regular PHP.
When your package is disabled, use method `cache_remove`, to remove previously
created PHP file.

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

General usage:

```php
public function __construct($tplp, $i18n)
{
    $template = $tplp->template('home_page');

    // Set data
    $template->data('username', 'Anonymous');

    // Set translator (if you use it)
    $template->set_translator($i18n->translator());

    // Process PHP with variables and return HTML
    $template->render(); // => HTML
}
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
{name|strtolower} // => <?php echo strtolower($name); ?>
// LEONARDO DA VINCI => leonardo da vinci
```

You can chain more functions together:

```
{name|strtolower|ucfirst} // => <?php echo ucfirst(strtolower($name)); ?>
// LEONARDO DA VINCI => Leonardo Da Vinci
```

You can pass arguments to functions:

```
{name|substr:0,8} // => <?php echo substr($name, 0, 8); ?>
// Leonardo Da Vinci => Leonardo
```

Arguments can be variables:

```
{description|wordwrap:blog[line_width],'<br/>'} // => <?php echo wordwrap($description, $blog['line_width'], '<br/>'); ?>
```

You can use functions with non variables also, types are accepted: numeric, boolean,
null, strings.

```
{24,im_function} => <?php echo im_function(24); ?>
{true,im_function} => <?php echo im_function(true); ?>
{null,im_function} => <?php echo im_function(null); ?>
{'Hello World!',im_function} => <?php echo im_function('Hello World!'); ?>
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

In if-elif statements you can use functions:

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

To use translations you need to set translator (with `set_translator`), then in
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

#### Comments

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

#### null __construct ( array $pkgm_trace )

This class will be automatically constructed for you package.

#### null set_translator ( object $translator )

If you wish your templates to be translated, then you need to send translator
object to template(s).

```php
public function __construct($tplp, $i18n)
{
    $tplp->set_translator($i18n->translator());

    // OR You can set translator individually to each template also:

    $template = $tplp->template('home_page');

    // Set for this particular template
    $template->set_translator($i18n->translator());
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

You don't need to construct this class directly, you can instead use `$tplp->template('template_name');`.

#### null __construct ( string $filename, object $translator = null )

Construct template, require _filename_ which need to be full absolute path to
the template's PHP file.

#### null set_translator ( object $translator )

Assign translator object to the template. If you did this in main object, then
you don't need to do it here. Example:

```php
$tplp->set_translator($i18n->translator());
$template = $tplp->template('home_page');
$template->render(); // => HTML

// OR, The same... You can set translator individually to each template also:

$template = $tplp->template('home_page');

// Set for this particular template
$template->set_translator($i18n->translator());
$template->render(); // => HTML
```

#### null data ( string|array $key, mixed $value = null)

You can set one or more variables used by template. Examples:

```php
$template->data('var', 'value');
$template->data([
    'var1' => 'val1',
    'var2' => 'val2',
    'var3' => 'val3'
]);
```

**Warning!** Your variables shouldn't start with `tplp_`, those are reserved.

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


### \Mysli\Tplp\InclusionsResolver

Please note, this class is used internally to resolve templates' inclusions.

#### null __construct ( array $templates )

Require an array, list of templates in format handle => string.

#### array resolve ( void )

Resolve templates' inclusions and return resolved list.
