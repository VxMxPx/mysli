# Tplp (Template Parser)

## Introduction

Tplp (Template Parser) package is a simple template parser. It's minimal on resources,
because it's run only once, for each package you enable - at that point it parse
all the template and save them as a regular _php_.

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
public function __construct($tplp)
{
    // TODO: Code example
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
TODO: It will though!

## Role

This package has a standard role `~tplp`.

You can make your own implementation of tplp package. You can extend the file
format, but make sure to fully support existing syntax.

Following methods are required:

| Return      | Method Name           | Parameters                             |
|-------------|-----------------------|----------------------------------------|

TODO: Required methods!

## API

TODO: API
