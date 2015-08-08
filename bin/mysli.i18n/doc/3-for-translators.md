# I18n For Translators

## General

When editing a file which has an extension .lng, you'll be presented with a
content like the one in the example below:

    # I'm a Comment!
    @WHATS_YOUR_NAME What's your name
    @LOGOUT Logout

Everything that **starts with a hash symbol (#)** is a **comment**. Comments are
there only to give you an idea of what's going on and/or to give you
further information and instructions. Comments will be entirely ignored by
the system and won't be displayed anywhere on the page.
**Do not translate comments.**

Text which **starts with an at symbol (@)**, written all in uppercase
is a **key**. In the example above, `@WHATS_YOUR_NAME` and `@LOGOUT` are keys.
They're used entirely by the system and will not be visible to the user
of the page. Keys don't need to be grammatically correct, neither they need to
be spelled correctly. Actually they're written in plain English only so that
it's easier for a programmer to get an idea what will be displayed at some
particular point. **Do not translate keys.**

Please note: you can't add your own keys, they're added in by a programmer,
as he decides where on a page particular key will be displayed.

Anything following the key (in the example above _What's your name_ and
_Logout_) is actually a text which will be displayed to the user. That's the
text you can freely edit and translate.

## Variables

Variables are points of a text which will be dynamically replaced.
In translation files they're defined as numbers (and sometimes as a text)
surrounded with a curly brackets, examples:

```
{1}
{1 login}
```

Why do we need variables? Consider the examples below:

```
@HELLO_USER  Welcome back {1}
@PLEAE_LOGIN Please {1 login here}
```

In the first example, we'll replace {1} with user's name, so the result
could be, for example: _Welcome back Marko_ or _Welcome back Eduard_.
When translating this string, we could be creative (if needed) and do so:
`Hi there {1} how are you?` which would result in `Hi there Inna how are you?`
(in case user name is Inna).

The second example is used when we know what the text will be, but we need to do
something particular with it. In this example, we need `login here` to be
a link, which will login the user. As in the example above, we can be creative,
and do so: `{1 Login here} please`, or `{1 Login}`.

There can be text, for example, which has more than one variable assigned:

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

Please note: you can't define your own variables, they're defined by a
programmer, he decide what a particular variable will represent.

Variables are local to a key, variable {1} will represent different things when
assigned to different keys. To understand this behavior better,
consider the following example:

```
@GOOD_DAY_NIGHT Good {1}!
@SKY_COLOR      The sky is {1}
```

For the first key, variable {1} could be replaced by words like `day` or
`night` resulting in `Good day!` or `Good night!` while in example two,
variable {1} could be replaced by `blue` resulting in `The sky is blue`.

## Pluralization

Pluralization is achieved by appending number in square brackets,
at the end of the key, for example:

```
@COMMENTS[0] No comments.
@COMMENTS[1] There's one comment.
```

The above example will display text `No comments.` when there's no comments and
`There's one comment.` when there's one comment.

You can add more keys yourself, if you need them. For example, Slovene language,
have additional to singular and plural form, also dual, so we could add:

```
@COMMENTS[2] 2 komentarja.
```

To cover all numbers greater than (and including) particular number,
you can add plus symbol to the left of the number:

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
@AGE[3...4]   Will
@AGE[5]       Purpose
@AGE[6...12]  Competence
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

Usually pluralized translations will contain variables,
which will look like this:

```
COMMENTS[2+] {n} comments
```

This simply mean, that when there's two or more comments, replace `{n}` with
number of comments. In case we have 12 comments, the above translation would
be displayed as `12 comments`.

## Multiline Text

It's permitted to put your translation into multiple lines, this is mostly done
for readability purposes when it comes to long strings.

All the examples bellow are valid:

```
@TOLSTOY Count Lev Nikolayevich Tolstoy, also known as Leo Tolstoy,
was a Russian writer who primarily wrote novels and short stories.

@TOLSTOY
Count Lev Nikolayevich Tolstoy,
also known as Leo Tolstoy,
was a Russian writer
who primarily wrote novels
and short stories.

@TOLSTOY

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
or hash (#) symbol at the begging of translation lines, e.g.:

```
@TOLSTOY
Count Lev Nikolayevich Tolstoy,
also known as Leo Tolstoy, was a
@RUSSIAN writer who primarily wrote novels
and short stories.
```

The above example will result in error, because `@RUSSIAN` will be
interpreted as a new key.

## Other

In some situation true/false value can be assigned to the key:

```
@LOGGEDIN[true]  You're logged in!
@LOGGEDIN[false] You're not logged in!
```
