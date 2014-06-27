# I18n: File Syntax and Usage

Please see the [For Translators](#for-translators) section for details of syntax.

The translation files needs to have `.mt` (mysli translation) extension,
and should be named accordingly to
[ISO 639-1 standards](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).
You should save them as `UTF-8`, `no BOM`, with `LF` line endings, preferably to
`i18n` folder in you package's root.

## General

The basic syntax is:

    # Comment
    @KEY Value

The key must always be in all upper case letters, prefixed with _at_ (@) symbol,
at the very beginning of the line. Between key and value needs to be at least
one space or tab.

You can access translation with `translate` method, required parameter is key,
which can be lower case, and without _at_ symbol:

```php
$translator->translate('key'); // => Value
```

## Variables

Use curly brackets to define a variable:

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
$translator->translate(
    'login',
    '<a href="#login">%s</a>'
); // => Please <a href="#login">login here</a>.
```

## Pluralization

Square brackets are used for pluralization, for example:

```
@COMMENTS[2] Two comments!
```

When calling _translate_ method, you should pass `$key` as an array, first element
being actual key, send second number. See the example bellow...

```php
$translator->translate(['comments', 2]); // => Two comments!
```

To cover all numbers greater (or smaller) than particular number, you can use
plus or minus symbol to the right of the number:

```
@COMMENTS[3+] Three or more comments!
@TEMPERATURE[0-] It's freezing!
```

```php
$translator->translate(['comments', 3]); // => Three or more comments!
$translator->translate(['comments', 23]); // => Three or more comments!
$translator->translate(['temperature', -12]); // => It's freezing!
```

You can also target particular range of numbers, putting three dots between two values:

```
@AGE[0...2] Hopes
@AGE[3...4] Will
```

```php
$translator->translate(['age', 1]); // => Hopes
$translator->translate(['age', 3]); // => Will
```

Using asterisk (*) symbol you can even match particular numeric patterns:

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

You can use variables in combination with pluralization:

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

## Multiline Text

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

If you want to preserve new lines, the `[n]` can be used in translation:

```
@LOREM[n] Lorem ipsum dolor sit amet,
consectetur
adipisicing elit.
```

```php
$translator->translate('lorem'); // => Lorem ipsum dolor sit amet, consectetur adipisicing elit.
```

## Other

In some situation true/false value can be assigned to the key:

```
@LOGGEDIN[true]  You're logged in!
@LOGGEDIN[false] You're not logged in!
```

```php
$translator->translate(['loggedin', true]); // => You're logged in!
$translator->translate(['loggedin', false]); // => You're not logged in!
```
