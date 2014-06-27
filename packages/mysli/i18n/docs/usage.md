# I18n: Usage

In your package root create a new directory with name `i18n` which will be
containing your language files.

The language files needs to have `.mt` extension, and should be named according to
[ISO 639-1 standards](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).

Example of such directory structure:

```
vendor/
    package/
        i18n/
            en.mt
            sl.mt
            ru.mt
```

When your package is enabled you can use method `create_cache`, which will parse
all files in `i18n` folder of your package, and saved them as regular JSON.
When your package is disabled, use method `remove_cache`, to remove previously
created JSON file.

Your package gets injected instance of i18n, which you can start using without
any further configuration.

Examples:

In your setup.php:

```php
public function before_enable()
{
    $this->i18n->create_cache();
    // If you've put your .mt files to different director than i18n, then you can
    // specify it when creating cache:
    // $this->i18n->create_cache('different_i18n_directory')
}
public function before_disable()
{
    $this->i18n->remove_cache();
}
```

General usage:

```php
public function __construct($i18n)
{
    // No need to specify anything, i18n require pkgm_trance when constructed,
    // and automatically load according language for your package.

    $translator = $i18n->translator();

    $hello = $translator->translate('hello_world');

    // Current language was read from configuration, but you can change it...
    $translator->primary('ru');
    $translator->secondary('en');
    $privet = $translator->translate('hello');
}
```
