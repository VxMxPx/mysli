# Tplp Usage

In your package root create a new directory `assets/tplp` which will be
containing template files.

The template files needs to have `.tpl.html` extension.
You should save them as `UTF-8`, `no BOM`, with `LF` line endings.

Example of such directory structure:

```
vendor.package/
    assets/
        tplp/
            layout.tpl.html
            sidebar.tpl.html
            list.tpl.html
```

... other, in class where templates are used:

```php
const __use = 'mysli.tpl.html';

$template = tplp::select('vendor.package');

// Set translator (if you use it)
$template->set_translator(i18n::select('vendor.package'));

// Process PHP with variables and return HTML
$template->render('template_name', $variables); // => HTML
```

**Note** the default path can be changed. Modify `mysli.pkg.ym` file and add:

```
tplp:
    path: new/path
```

The path will be relative to the package's root.
