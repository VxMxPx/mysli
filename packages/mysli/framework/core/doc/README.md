# Mysli Framework Core

## Introduction

Mysli Core provide core functions like: `dump`, `is_cli` and  `__use`.
Additional to that it provide `autoloader` and `inject` functionality.

The `__init` method will define two core paths: `MYSLI_PKGPATH` (packages root)
and `MYSLI_DATPATH` (database root).

The `__init` method will take following arguments:

    $datpath    Database root
    $pkgpath    Packages root
    $autoloader
        Default: ['\\mysli\\framework\\core\\autoloader', 'load']
        Autoloader class and method (must be loaded).
    $injector
        Default: '\\mysli\\framework\\core\\inject'
        Injector class.

### Usage

Core will be loaded by `web` (in index.php) or `cli` (in ./dot) packages
automatically.

The common functions provided by core are:

```php
dump();    // print_r with <pre> if in web, or \n if in cli, and die.
dump_r();  // like dump but will not die.
dump_rr(); // like dump_r but will return instead of print.

is_cli(); // determine if this is command line interface.
```

The most importantly, the `inject` class is provided, which can be used to
include various classes:

```php
\inject::to(__namespace__)
->from('mysli/util/config');
```

... more common way to achieve the same is to use alias `__use` instead:

```php
__use(__namespace__,
    'mysli/util/config'
);
```

The first argument is always `__namespace__` which will tell injector to which
namespace are we injecting particular class. The rest of arguments are classes
we want to inject.

**Note** that slashes (/) are used rather than backslashes (\\).

The above example specified only package name: `mysli/util/config`, where
actual class would be `mysli\\util\\config\\config`. This is automatically
resolved for you.

Examples...

Regular class in package:

    Argument:     'vendor/package'
    Resolved to:  \\vendor\\package\\package
    Available as: package

Meta package (like mysli/util/config):

    Argument:     'vendor/meta/package'
    Resolved to:  \\vendor\\meta\\package\\package
    Available as: package

... actual example:

    Argument:     'mysli/util/config'
    Resolved to:  \\mysli\\util\\config
    Available as: config
    Example:      config::select('me/my_package');

Class in sub-directory:

    Argument:     'vendor/package/sub_dir/class'
    Resolved to:  \\vendor\\package\\sub_dir\\class
    Available as: class

Specific class:

    Argument:     'vendor/package/class'
    Resolved to:  \\vendor\\package\\class
    Available as: class


Alias class to different name:

    Argument:     ['vendor/package/class' => 'my_class']
    Resolved to:  \\vendor\\package\\class
    Available as: my_class

Specific multiple classes:

    Argument:     'vendor/package/{class1,class2}'
    Resolved to:  \\vendor\\package\\class1 (and) \\vendor\\package\\class2
    Available as: class1 (and) class2

Alias multiple classes:

    Argument:     ['vendor/package/{class1,class2}' => '{my_class1,my_class2}']
    Resolved to:  \\vendor\\package\\class1 (and) \\vendor\\package\\class2
    Available as: my_class1 (and) my_class2

Alias class to different name in namespace:

    Argument:     ['vendor/package/class' => 'namespace/my_class']
    Resolved to:  \\vendor\\package\\class
    Available as: namespace\\my_class

Load all classes in a directory:

    Argument:     'vendor/package/sub/*'
    Resolved to:  \\vendor\\package\\sub\\... (all files in a directory)
    Available as: ... (all loaded classes)

Load and alias (to different namespace) all classes in a directory:

    Argument:     ['vendor/package/sub/*' => 'namespace/%s']
    Resolved to:  \\vendor\\package\\sub\\... (all files in a directory)
    Available as: namespace\\... (all loaded classes)

Load class from current package:

    From:         vendor/my_package
    Argument:     './special_class'
    Resolved to:  \\vendor\\my_package\\special_class
    Available as: special_class

... can be used to load all sub classes for example:

    From:         vendor/my_package
    Argument:     './sub/*'
    Resolved to:  \\vendor\\my_package\\sub\\... (all files)
    Available as: ... (all loaded classes)

Load class from same namespace (vendor + meta):

    From:         vendor/my_package
    Argument:     '../other_package/class'
    Resolved to:  \\vendor\\other_package\\class
    Available as: class

... actual example:

    From:         mysli/util/config
    Argument:     '../json'
    Resolved to:  mysli\\framework\\json\\json
    Available as: json

## License

The Mysli Framework Core is licensed under the GPL-3.0 or later.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
