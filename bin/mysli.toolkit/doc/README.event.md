# Mysli Framework Event

## Introduction

The Event library helps you manage events.

## Usage

Include it with `__use(__namespace__, 'mysli/framework/event');`.

To wait for an event to be triggered use `on` method:

```php
event::on('vendor/package/class:action', function () {
    // Hello world!
});
```

... this can be canceled with `off`:

```php
$id = event::on('vendor/package/class:action', function () {});
event::off('vendor/package/event', $id);
```

For your library to be called automatically whenever an event is
triggered you can use `register` and provide (namespaced) class and
method:

```php
event::register('vendor/package/class:action', 'me\\package\\class::method');
```

... in same way you can remove it with `unregister` method:

```php
event::unregister('vendor/package/class:action', 'me\\package\\class::method');
```

To trigger an event use `trigger`:

```php
event::trigger('vendor/package/my_class:action', $optional_parameters_array);
```

## Naming conventions

The even should be named the same as you class is (though slashes
not backslashes). Action should be appended by colon:

```php
event::trigger('vendor/package/class:action');
```

## License

The Mysli Framework Event is licensed under the GPL-3.0 or later.

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
