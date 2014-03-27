# Tplp (Template Parser)

## Introduction

Tplp (Template Parser) package is a simple template parser.
It's minimal on resources - run only once, for each package you enable.
All templates are parsed and saved as a regular PHP.

##  Usage

In your package root create a new directory with name `templates` which will be
containing template files.

The template files needs to have `.tplm.html` extension.

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

## Configuration

The following configurations are available:

| Key   | Default | Description                                |
|-------|---------|--------------------------------------------|
| debug | false   | If set to `true`, then template will be processed on each request rather than just when package is enable. |

## Template Syntax

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
