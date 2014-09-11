# I18n: API

## \Mysli\I18n

### null __construct ( array $pkg_trace, object $config )

This class will be automatically constructed for you package.

### object translator ( void )

Get translator object. Return `\Mysli\I18n\Translator`.

### boolean create_cache ( string $folder = 'i18n' )

Create cache for current package.

### boolean  remove_cache ( void )

Remove cache for current package.

## \Mysli\I18n\Translator

### null __construct ( array $dictionary, string $primary, string $secondary )

You don't need to manually construct translator, you can just use `$i18n->translator()`
and dictionaries + languages will be automatically set for your package.

```
$translator = new \Mysli\I18n\Translator($dictionary, 'ru', null);
// Better...
$translator = $i18n->translator();
```

### integer exists ( string $language )

Check if particular language exists in a dictionary. Return number of keys
for particular language, 0 if language doesn't exists.

```php
$translator->exists('ru'); // 230

if ( ! $translator->exists('ru')) {
    // Do something
}

if ($translator->exists('ru') === 0) {
    // Do something
}
```

### string primary ( string $language = null )

Set primary language for translations.
This will be automatically set, when the translator is constructed
(value from i18n, from config).

```php
$translator->primary('ru');
$translator->primary(); // => ru
```

### string secondary ( string $language = null )

Set / Get secondary language, if primary language not found.
This will be automatically set, when the translator is constructed
(value from i18n, from config).

```php
$translator->secondary('en');
$translator->secondary(); // => en
```

### array as_array ( void )

Return dictionary (cache) as an array.

### string translate ( string|array $key, array $variable = [] )

Translate the key! If key not found in either primary or fallback language cache,
null is returned.

```php
// Simple key
// .mt = @HELLO Hello!
$translator->translate('hello'); // => Hello!

// Key with variables
// .mt = @HELLO_MY_NAME_IS Hello, my name is {1}.
$translator->translate('hello_my_name_is', ['Lada']); // => Hello, my name is Lada.

// Key with enumeration
// .mt = @COMMENTS[0] No comments
// .mt = @COMMENTS[1] One comment.
$translator->translate(['comments', 0]); // => No comments.
// More than ...
// .mt = @COMMENTS[1+] {n} comments.
$translator->translate(['comments', 3]); // => 3 comments.
// Less than ...
// .mt = @TEMPERATURE[0-] It's {1}°C bellow the zero.
$translator->translate(['temperature', -12], [12]); // => It's 12°C bellow the zero.
// Range
// .mt = @AGE[10...19] Teenage!
$translator->translate(['age', 15]); // => Teenage!

// Boolean key
// .mt = @LOGGEDIN[true]  You're logged in!
// .mt = @LOGGEDIN[false] You're NOT logged in!
$translator->translate(['loggedin', false]); // => You're NOT logged in!
```

### string translate_as ( string|array $key, $language, array $variable = [] )

Translate the ket to particular language. If key not found, null is returned.

```php
$translator->translate('hello', 'ru'); // => privet
```

## \Mysli\I18n\Parser

### (static) array parse ( string $trasnaltion )

Process Mysli Translation (mt) and return an array.

```php
$translation = <<<EOD
@HELLO hello
@WORLD world
EOD;

\Mysli\I18n\Parser::parse($translation); // =>
// [
//     '.meta' => [
//         'created_on' => 20140327104900, // gmdate('YmdHis')
//         'modified'   => false
//     ],
//     'HELLO' => 'hello',
//     'WORLD' => 'world'
// ]
```
