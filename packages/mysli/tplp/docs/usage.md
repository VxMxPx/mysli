# Tplp: Usage

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

## Setup

When your package is enabled you can use method `create_cache`, which will parse
all files in `templates` folder of your package, and saved them as a regular PHP.
When your package is disabled, use method `remove_cache`, to remove previously
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

## General

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
    $template->set_translator($i18n->translator());

    // Process PHP with variables and return HTML
    $template->render(); // => HTML
}
```

## Functions and Variables

Individual templates are separate objects. You can set variables and functions,
which are local to just one template - for example _user_edit_ can have different
functions and variables than _user_create_.

You can register functions and variables on three levels: `global`, `package` and
`template`.

### Global

Globally registered functions and variables will be passed to all packages.
To register function globally use `$tplp->register_function($name, $callable)`.
To unregister global function use: `$tplp->unregister_function($name)`. You can
use `register_variable` and `unregister_variable` for variables. Example:

```php
// Package 1
$tplp->register_variable('title', 'My Package #1');

// Package 2
// Variable is available in second package
$tplp->get_variable('title'); // => 'My Package #1';

$template = $tplp->template('index');
// Variable is available in individual template of second package.
$template->get_variable('title'); // => 'My Package #1';
// You can rewrite variable at any level...
$template->set_variable('title', 'Different Title!');
```

### Package

You can use `$tplp->set_function($name, $callable)` and `$tplp->set_variable($name, $callable)`;
when you request individual template, all the functions and variables defined will be passed on.

The difference between _package_ and _global_ level is, that function/variables defined on
package level, will **not** be available in other packages.

Examples:

```php
// Package 1
$tplp->set_variable('title', 'My Package ::');
$login = $tplp->template('login');
$login->get_variable('title'); // => My Package ::
$register = $tplp->template('register');
$register->get_variable('title'); // => My Package ::

// Package 2
$tplp->get_variable('title'); // => null
```

### Template

Finally, the template level; functions and variables defined here, will be available
only to this particular template.

Examples:

```php
$login = $tplp->template('login');
$login->set_variable('title', 'My Package :: Login');
$login->get_variable('title'); // => My Package :: Login

$register = $tplp->template('register');
$register->get_variable('title'); // => null
$register->set_variable('title', 'My Package :: Register');
$register->get_variable('title'); // => My Package :: Register
```
