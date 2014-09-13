# Mysli Framework Config

## Introduction

Conig for Mysli packages:

## Usage

To include it use: `__use(__namespace__, 'mysli/framework/config');`.

To select your package's config use `select`, which will return config object:

```php
$config = config::select('vendor/package')
```

Then use `get` method:

```php
$title = $config->get('title');
```

To set a new value, use `set` method:

```php
$config->set('title', 'New Title');
// don't forget to write changes to file...
$config->save();
```

## License

The Mysli Framework Config is licensed under the GPL-3.0 or later.

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
