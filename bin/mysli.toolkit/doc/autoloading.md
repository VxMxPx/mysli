# Autoloading

## General

Because classes and namespaces are lowercase they can be mapped almost directly
to filenames, without special conversions needed.

One difference between namespace and an actual filename is `lib` segment,
which is assumed and hence can be omitted.

For example, class `mysli\toolkit\router` translates to
`mysli/toolkit/lib/router.php` (note the `lib` part being added).

There are special cases (like scripts and tests), which are not located in a
`lib` directory, but rather in a package's root.
In those cases, namespace must include `root` segment, after the packages name,
so: `vendor\package\root\folder\file.php`.
For example, class located in `mysli/toolkit/script/interactive.php`
would be namespaced as: `mysli\toolkit\root\script\interactive` (note `root` being
added).

## Examples

Examples of how various namespaces would get mapped to filenames:

    mysli\toolkit\event   => mysli/toolkit/lib/event.php
    mysli\toolkit\fs\file => mysli/toolkit/lib/fs/file.php
    mysli\toolkit\root\script\interactive => mysli/toolkit/script/interactive.php

## Special cases

Because root segment is translated to the root directory of package, it would
be valid (although unnecessary) to namespace a class: `mysli\toolkit\root\lib\event`,
which would be the same as `mysli\toolkit\event`.

## Advanced

Autoloader is loaded and registered by Toolkit __init class.

Mysli is a platform which entirely consists of packages (Toolkit itself is
a package).

When a class, for example `/foo/bar/baz` is requested, here's what happens:

1. Resolve package's name from namespaced class.
In this example: \foo\bar\baz => foo.bar
2. See if package was NOT initialized before, in such case, look for an
__init.php file. If such file exists, include it.
3. See if there's `__use` constant defined, if yes, resolve all statements
written there (by aliasing external classes to namespace of class which
defined __use statement).
4. See if there's `__init` static method in class, if yes, call it.
5. Include actual class that was requested (baz).
6. Resolve __use statement.
7. See if there's `__init` method in this class, and call it.

To simplify, here's a list of checks autoloader will do, when for example
method `\vendor\package\foo::bar` is called:

```
vendor.package/ (!)
    lib/__init.php (?)
        const \vendor\package\__init\__use (?)
        \vendor\package\__init::__init() (?)
    lib/foo.php (!)
        const \vendor\package\foo\__use (?)
        \vendor\package\foo::__init() (?)
        \vendor\package\foo::bar() (!)

(!) = Required, will failed if not found
(?) = Optional
```

All initializations are done only once per request, if class method is called
the second time, class it will not be initialized again, the same is true for
package itself.
