# I18n (Internationalization)

- [Introduction](#introduction)
- [Usage](#usage)
- [Configuration](#configuration)
- [File Syntax and Usage](#file-syntax-and-usage)
- [For Translators](#for-translators)
  - [General](#general-1)
  - [Variables](#variables-1)
  - [Pluralization](#pluralization-1)
  - [Multiline Text](#multiline-text-1)
  - [Other](#other-1)
- [Events](#events)
- [Role Specifications](#role-specifications)
- [API](#api)

## Introduction

I18n offers internationalization and localization support for packages.
Special `.mt` (mysli translation) files with a simple syntax are used.
Those files gets cached / converted to regular JSON.

##  Usage

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

When your package is enabled you can use method `cache_create`, which will parse
all files in `i18n` folder of your package, and saved them as regular JSON.
When your package is disabled, use method `cache_remove`, to remove previously
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
    $hello = $i18n->translate('hello_world');

    // Current language was read from configuration, but you can change it...
    $i18n->set_language('ru');
    $i18n->set_fallback_language('en');
    $privet = $i18n->translate('hello');
}
```

## Configuration

The following configurations are available:

| Key               | Default | Description                                |
|-------------------|---------|--------------------------------------------|
| primary_language  | en      | The language which is primarily used.      |
| fallback_language | null    | Fallback language, if primary not found.   |

## File Syntax and Usage

Please see the [For Translators](#for-translators) section for details of syntax.

The translation files needs to have `.mt` (mysli translation) extension,
and should be named accordingly to
[ISO 639-1 standards](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).
You should save them as `UTF-8`, `no BOM`, with `LF` line endings, preferably to
`i18n` folder in you package's root.

### General

The basic syntax is:

```
# Comment
@KEY Value
```

The key must always be in all upper case letters, prefixed with _at_ (@) symbol,
at the very beginning of the line. Between key and value needs to be at least
one space or tab.

You can access translation with `translate` method, required parameter is key,
which can be lower case, and without _at_ symbol:

```php
$i18n->translate('key'); // => Value
```

### Variables

Use curly brackets to define a variable:

```
@GREET_USER Hello {1}.
```

```php
$i18n->translate('greet_user', 'Jasna'); // => Hello Jasna.
```

Multiple variables:

```
@GREET_USER_AND_AGE Hello {1}, you're {2} years old.
```

```php
$i18n->translate('greet_user', ['Jasna', 21]); // => Hello Jasna, you're 21 years old.
```

Use strings inside variables:

```
@LOGIN Please {1 login here}.
```

```php
$i18n->translate(
    'login',
    '<a href="#login">%s</a>'
); // => Please <a href="#login">login here</a>.
```

### Pluralization



### Multiline Text
### Other

## For Translators

### General

When editing a file which has an extension .mt, you'll be presented with a content
like the one in the example below:

```
# I'm a Comment!
@WHATS_YOUR_NAME What's your name
@LOGOUT Logout
```

Everything that **starts with a hash symbol (#)** is a **comment**. Comments are there
only to give you an idea of what's going on and / or to give you the further
information and instructions.
Comments will be entirely ignored by the system and won't be displayed anywhere
on the page. **Do not translate comments.**

The text which **starts with an at symbol (@)**, written all in uppercase is a **key**.
In the example above, @WHATS_YOUR_NAME and @LOGOUT are keys. They're used entirely
by the system and will not be visible to the user of the page. Keys don't need to be
grammatically correct, neither they need to be spelled correctly.
Actually they're written in plain English only so that it's easier for a programmer
to get an idea what will be displayed at some particular point. **Do not translate keys.**

Anything following the key (in the example above _What's your name_ and _Logout_)
is actually a text which will be displayed to the user.
That's the text you can freely edit and translate.

Please note: you can't add your own keys, they're added in by a programmer,
as he decides where on a page particular key will be displayed.

### Variables

Variables are points of a text which will be dynamically replaced. In translation
files they're defined as numbers (and sometimes as a text) surrounded with a curly brackets, examples:

```
{1}
{1 login}
```

Why do we need variables? Consider the examples below:

```
@HELLO_USER  Welcome back {1}
@PLEAE_LOGIN Please {1 login here}
```

In the first example, we'll replace {1} with user's name, so the result could be, for example:
_Welcome back Mark_ or _Welcome back Eduard_.
When translating this string, we could be creative (if needed) and do so:
`Hi there {1} how are you?` getting displayed as `Hi there Inna how are you?`
(in case user name is Inna).

The second example is used when we know what the text will be, but we need to do
something particular with it. In this example, we need `login here` to be a link,
which will login the user. As in the example above, we can be creative, and do so:
`{1 Login here} please`, or `{1 Login}`.

There can be a text, for example, which has more than one variable assigned:

```
@USER_NAME_AND_AGE Your name is {1} and you're {2} years old.
```

Being displayed as (in case we have user Inna, 12):
`Your name is Inna and you're 12 years old`.

Be careful about numbers as they do have a significant meaning,
consider the above example, only that `{2}` was replaced with `{1}`:

```
@USER_NAME_AND_AGE Your name is {1} and you're {1} years old.
```

Resulting in: `Your name is Inna and you’re Inna years old.`

Please note: you can't define your own variables, they're defined by a programmer,
he decide what a particular variable will represent.

Variables are local to a key - variable {1} will represent different things when
assigned to different keys. To understand this behavior better, see the example below:

```
@GOOD_DAY_NIGHT Good {1}!
@SKY_COLOR      The sky is {1}
```

For the first key, variable {1} will be replaced by a word `day` or `night` resulting
in `Good day!` or `Good night!` while in example two, variable {1} will be replaced
by `blue` resulting in `The sky is blue`.

### Pluralization

Pluralization achieved by appending number in square brackets,
at the end of the key, for example:

```
@COMMENTS[0] No comments.
@COMMENTS[1] There's one comment.
```

The above example will display text `No comments.` when there's no comments and
`There's one comment.` when there's one comment.

You can add more keys yourself, if you need them. For example, Slovene language,
have additional to singular and plural form, also dual, so I could add:

```
@COMMENTS[2] 2 komentarja.
```

To cover all numbers greater than particular number, you can add plus
symbol to the left of the number:

```
@COMMENTS[3+] Wow, there are three or more comments!
```

You can do the same for negative, using minus:

```
@TEMPERATURE[0-] It's freezing!
```

You can also target particular range of numbers, putting three dots between two
values:

```
@AGE[0...2]   Hopes
@AGE[2...4]   Will
@AGE[4...5]   Purpose
@AGE[5...12]  Competence
@AGE[13...19] Fidelity
@AGE[20...39] Love
@AGE[40...64] Care
@AGE[65+]     Wisdom
```

You can target particular pattern with asterisk (*), for example,
match all the numbers ending with 7:

```
@COMMENTS[*7]  I'm ending with 7!
@COMMENTS[4*]  I'm starting with 4!
@COMMENTS[1*2] I'm starting with 1 and ending with 2!
```

You can combine more than one rules using comma:

```
@ODD[*1,*3,*5,*7,*9] I'm odd! :S
@TWO_AND_NINE[2,9]   I'm either two or nine!
```

Usually pluralized translations will contain variables, which will look like this:

```
COMMENTS[2+] {n} comments
```

This simply mean, that when there's two or more comments, replace `{n}` with
number of comments. In case we have 12 comments, the above translation would
be displayed as `12 comments`.

### Multiline Text

It's permitted to put your translation into multiple lines, this is mostly done
for readability purposes when it comes to long strings.

All the examples bellow are valid:

```
@TOLSTOY[1] Count Lev Nikolayevich Tolstoy, also known as Leo Tolstoy,
was a Russian writer who primarily wrote novels and short stories.

@TOLSTOY[2]
Count Lev Nikolayevich Tolstoy,
also known as Leo Tolstoy,
was a Russian writer
who primarily wrote novels
and short stories.

@TOLSTOY[3]

Count Lev Nikolayevich Tolstoy,
also known as Leo Tolstoy,
was a Russian writer
who primarily wrote novels
and short stories.
```

The result of all the above cases will be exactly the same, the text will be
transformed to single line:

```
Count Lev Nikolayevich Tolstoy, also known as Leo Tolstoy, was a Russian writer who primarily wrote novels and short stories.
```

If you want to preserve line breaks, you can use `nl` directive:

```
@TOLSTOY[nl]
Count Lev Nikolayevich Tolstoy,
also known as Leo Tolstoy,
was a Russian writer
who primarily wrote novels
and short stories.
```

This will result in:

```
Count Lev Nikolayevich Tolstoy,
also known as Leo Tolstoy,
was a Russian writer
who primarily wrote novels
and short stories.
```

***WARNING*** when using multiline text, you shouldn't put _at_ (@),
or hash (#) symbol at the begging of the line!

### Other

In some situation true/false value can be assigned to the key:

```
@LOGGEDIN[true]  You're logged in!
@LOGGEDIN[false] You're not logged in!
```

## Events

This package emits no events.

## Role

This package has a standard role `~i18n`.

You can make your own implementation of i18n package. You can extend the file
format, but make sure to fully support existing syntax.

Following methods are required:

| Return      | Method Name           | Parameters                             |
|-------------|-----------------------|----------------------------------------|
| integer     | exists                | string $language                       |
| null        | set_language          | string $language                       |
| null        | set_fallback_language | string $language                       |
| array       | cache_as_array        | void                                   |
| boolean     | cache_create          | string $folder                         |
| boolean     | cache_remove          | void                                   |
| string      | translate             | string,array $key, array $variable     |
| array       | mt_to_array           | string $mt                             |

## API

### integer exists ( string $language )

Check if particular language exists in cache. Return number of keys
for particular language, 0 if language doesn't exists.

```php
$i18n->exists('ru'); // 230

if ( ! $i18n->exists('ru')) {
    // Do something
}

if ($i18n->exists('ru') === 0) {
    // Do something
}
```

### null set_language ( string $language )

Set primary language for translations. This will be automatically set, when the
i18n is constructed (value read from settings).

```php
$i18n->set_language('ru');
```

### null set_fallback_language ( string $language )

Set fallback language, if primary language not found. This will be automatically set,
when the i18n is constructed (value read from settings).

```php
$i18n->set_fallback_language('en');
```

### array cache_as_array ( void )

Return cache as an array.

### boolean cache_create ( string $folder = 'i18n' )

Create cache for current package.

### boolean  cache_remove ( void )

Remove cache for current package.

### string translate ( string|array $key, array $variable = [] )

Translate the key! If key not found in either primary or fallback language cache,
null is returned.

```php
// Simple key
// .mt = @HELLO Hello!
$i18n->translate('hello'); // => Hello!

// Key with variables
// .mt = @HELLO_MY_NAME_IS Hello, my name is {1}.
$i18n->translate('hello_my_name_is', ['Lada']); // => Hello, my name is Lada.

// Key with enumeration
// .mt = @COMMENTS[0] No comments
// .mt = @COMMENTS[1] One comment.
$i18n->translate(['comments', 0]); // => No comments.
// More than ...
// .mt = @COMMENTS[1+] {1} comments.
$i18n->translate(['comments', 3], [3]); // => 3 comments.
// Less than ...
// .mt = @TEMPERATURE[0-] It's {1}°C bellow the zero.
$i18n->translate(['temperature', -12], [12]); // => It's 12°C bellow the zero.
// Range
// .mt = @AGE[10...19] Teenage!
$i18n->translate(['age', 15]); // => Teenage!

// Boolean key
// .mt = @LOGGEDIN[true]  You're logged in!
// .mt = @LOGGEDIN[false] You're NOT logged in!
$i18n->translate(['loggedin', false]); // => You're NOT logged in!
```

### array mt_to_array ( string $mt )

Convert Mysli Translation (mt) to array.
