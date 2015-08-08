# I18n Usage

The language files need to have `.lng` extension, and should be named using
an IETF language tag. (@see mysli\i18n\locales)

Further reading on IETF:

https://en.wikipedia.org/wiki/IETF_language_tag
http://cldr.unicode.org/

Language files usually reside in a `i18n` folder, in case of theme, that's
in theme's root directory. In case of package, that's in `assets` directory.

Example of directory structure for package:

```
vendor.package/
    assets/
        i18n/
            en-us.lng
            sl.lng
            ru.lng
            ...
```

**Note** the default path can be changed. Modify `mysli.pkg.ym` file and add:

```
i18n:
    path: new/path
```

## Command Line

Command line interface can be used to pre-process language files.
Pre-processing will convert `lng` to `json` and saved them into `~dist`
directory of languages root (see above).
This will create permanent cache, and hence processing of `lng` file on first
request will not be necessary. It's recommended to produce cached version of
language on package's build.

Use `mysli i18n -h` for instructions.

## Using Languages in Package

Include `i18n` and use `select` to get translator instance. This will cache an
instance so that in can be used in all cases of a package:

```php
// Initial...
$translator = i18n::select('vendor.package');
$translator->primary('sl');
$translator->secondary('ru');
$translator->load('vendor.package');
$translator->translate('hello');

// Later...
$translator = i18n::select('vendor.package');
$translator->translate('hello');

// Or, to translate directly...
i18n::select('vendor.package', 'hello');
```

The above example will, in first line, create an unique translator (provided an
unique ID, best practice is package's name).

Translator instance can have two languages, primary and fallback - secondary.
If requested key is not found in a dictionary of a primary language, secondary
will be used. Fallback language is optional.

Both languages can be set on initial request, in such case, above example would
look like this:

```php
$translator = i18n::select(['vendor.package', 'sl', 'ru']);
```

Once translator is constructed, dictionaries can be appended (with `append`) or
loaded (with `load`). In above example dictionaries for both, primary and secondary
language will be loaded from `vendor.package` internationalization root.
Alternatively, full absolute path can be specified. Loaded dictionaries will
be automatically appended.

If dictionary doesn't exists in `json` format, it will be automatically processed
and saved to the temporary folder.

## File Syntax and Usage

Please see the _For Translators_ document for details of `lng` files syntax.

The translation files needs to have `.lng` extension, and should be saved as
`UTF-8`, `no BOM`, with `LF` line endings, preferably to `assets/i18n` folder
of package's root.

## General

The basic syntax is:

    # Comment
    @KEY Value

The key must always be in all upper case letters, prefixed with _at_ (@) symbol,
at the very beginning of the line. Between key and value needs to be at least
one space or tab.

Translation can be accessed with `translate` method, required parameter is key,
which can be lower case, and without _at_ symbol:

```php
$translator->translate('key'); // => Value
```

### Variables

Curly brackets can be used to define a variable:

```
@GREET_USER Hello {1}.
```

```php
$translator->translate('greet_user', 'Jasna'); // => Hello Jasna.
```

Multiple variables:

```
@GREET_USER_AND_AGE Hello {1}, you're {2} years old.
```

```php
$translator->translate('greet_user', ['Jasna', 21]); // => Hello Jasna, you're 21 years old.
```

Use strings inside variables:

```
@LOGIN Please {1 login here}.
```

```php
$translator->translate( 'login', '<a href="#login">%s</a>' );
// => Please <a href="#login">login here</a>.
```

### Pluralization

Square brackets are used for pluralization, for example:

```
@COMMENTS[2] Two comments!
```

When calling _translate_ method, `$key` should be passed as an array, first
element being actual key, send second a number. For example:

```php
$translator->translate(['comments', 2]); // => Two comments!
```

To cover all numbers greater (or smaller) than particular number,
plus or minus symbol to the right of the number can be used:

```
@COMMENTS[3+] Three or more comments!
@TEMPERATURE[0-] It's freezing!
```

```php
$translator->translate(['comments', 3]); // => Three or more comments!
$translator->translate(['comments', 23]); // => Three or more comments!
$translator->translate(['temperature', -12]); // => It's freezing!
```

Particular range of numbers can be targeted,putting three dots between two values:

```
@AGE[0...2] Hopes
@AGE[3...4] Will
```

```php
$translator->translate(['age', 1]); // => Hopes
$translator->translate(['age', 3]); // => Will
```

Using asterisk (*) symbol can match a particular numeric patterns:

```
@NUM[*7]  I'm ending with 7!
@NUM[4*]  I'm starting with 4!
@NUM[1*2] I'm starting with 1 and ending with 2!
```

```php
$translator->translate(['num', 7]); // => I'm ending with 7!
$translator->translate(['num', -7]); // => I'm ending with 7!
$translator->translate(['num', 1127]); // => I'm ending with 7!
$translator->translate(['num', 43]); // => I'm starting with 4!
$translator->translate(['num', 1992]); // => I'm starting with 1 and ending with 2!
```

Variables can be used in combination with pluralization:

```
@COMMENTS[2+] {1} Comments
```

```php
$translator->translate(['comments', 6], 6); // => 6 Comments
```

Note, in the example above, the first parameter is an array, containing key,
and number used for pluralization; second parameter is an actual variable!
This repetition can be avoided using `{n}` in the translation:

```
@COMMENTS[2+] {n} Comments
```

```php
$translator->translate(['comments', 6]); // => 6 Comments
```

It's possible to combine multiple rules, using comma:

```
@NUMBERS[12,24] I'm either 12 or 24!
@NUMBERS[*1,*3,*5,*7,*9] I'm odd!
```

```php
$translator->translate(['numbers', 12]); // => I'm either 12 or 24!
$translator->translate(['numbers', 5]); // => I'm odd!
$translator->translate(['numbers', 4537]); // => I'm odd!
```

### Multiline Text

Translations can be written in multiple lines, and will be transformed to single
line (kind of a like in HTML):

```
@LOREM Lorem ipsum dolor sit amet,
consectetur adipisicing elit.

@IPSUM
Ipsum dolor sit amet,
consectetur
adipisicing elit.
```

```php
$translator->translate('lorem'); // => Lorem ipsum dolor sit amet, consectetur adipisicing elit.
$translator->translate('ipsum'); // => Ipsum dolor sit amet, consectetur adipisicing elit.
```

To preserve new lines `[n]` can be used in translation:

```
@LOREM[n] Lorem ipsum dolor sit amet,
consectetur
adipisicing elit.
```

```php
$translator->translate('lorem'); // =>
// Lorem ipsum dolor sit amet,
// consectetur
// adipisicing elit.
```

### Other

In some situation true/false value can be assigned to the key:

```
@LOGGEDIN[true]  You're logged in!
@LOGGEDIN[false] You're not logged in!
```

```php
$translator->translate(['loggedin', true]); // => You're logged in!
$translator->translate(['loggedin', false]); // => You're not logged in!
```
