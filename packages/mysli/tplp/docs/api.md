# Tplp: API

## \Mysli\Tplp

use \Mysli\Tplp\ExtData;

### null __construct ( array $pkgm_trace )

This class will be automatically constructed for you package.

### null static register_function ( string $name, callable $function )

Add globally available function. This function will be available to all templates
and packages.

```php
\Mysli\Tplp\Tplp::register_function('my_func', function ($param) {
    return $param;
});
```

### null static unregister_function ( string $name )

Remove globally available function.

### null register_variable ( string $name, mixed $value )

Add globally available variable. This variable will be available to all templates
and packages.

```php
\Mysli\Tplp\Tplp::register_variable('my_variable', 'Hello world!');
$login = $tplp->template('login');
$login->get_variable('my_variable'); // => Hello world!
```

### null static unregister_variable ( string $name )

Remove globally available variable.

### null set_translator ( object $translator )

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

### object template( string $name )

Get template by name. Return `\Mysli\Tplp\Template` object.

```php
$template = $tplp->template('index');
$template->render(); // => HTML
```

### integer create_cache ( string $folder = 'templates' )

Create cache for current package.

### boolean remove_cache ( void )

Remove cache for current package.


## \Mysli\Tplp\Template

use \Mysli\Tplp\ExtData;

You don't need to construct this class directly, you can instead use `$tplp->template('template_name');`.

### null __construct ( string $filename, object $translator = null, array $variables = [], array $functions = [] )

Construct template, require _filename_ which need to be full absolute path to
the template's PHP file.

### null set_translator ( object $translator )

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

### string render ( void )

Process template, execute code with variables, and return result (HTML).

### string php ( void )

Return template's unprocessed PHP code.


## \Mysli\Tplp\Parser

Please note, this class is used internally to parse template (string).

### null __construct ( string $template )

Require string - actual template.

### string parse ( void )

Return parsed template.

## \Mysli\Tplp\ExtData

### null set_function ( string $name, callable $function )

Set a costume new function.

```php
$template->set_function('my_function', function ($param) {
    return $param;
});
```

If function is set on `tplp` object, then all templates (of package) will receive
that function.

**Warning!** Your functions shouldn't start with `tplp_`, those are reserved.

### null remove_variable ( string $name )

Remove particular function.

### null set_variable ( string|array $name, mixed $value = null )

You can set one or more variables used by template. Examples:

```php
$template->set_variable('var', 'value');
$template->set_variable([
    'var1' => 'val1',
    'var2' => 'val2',
    'var3' => 'val3'
]);
```

If variable is set on `tplp` object, then all templates (of package) will receive
that variable.

**Warning!** Your variables shouldn't start with `tplp_`, those are reserved.

### null remove_variable ( string $name )

Remove particular variable.

### mixed get_variable ( string $name )

Get variable's value if set, null otherwise.

## \Mysli\Tplp\InclusionsResolver

Please note, this class is used internally to resolve templates' inclusions.

### null __construct ( array $templates )

Require an array, list of templates in format handle => string.

### array resolve ( void )

Resolve templates' inclusions and return resolved list.
