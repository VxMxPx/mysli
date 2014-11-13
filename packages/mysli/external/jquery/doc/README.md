# Mysli External jQuery

## Introduction

This will allow you to either grab link to development or production (minimized)
version of jQuery from their CDN. jQuery source is actually **not** part of this
library.

If you have enabled cURL, and set `local` setting in this library to true,
the file(s) will be automatically fetched for you on first request, and
from that point on loaded from you local server.

Please note that jQuery source is
[licensed under MIT license](https://jquery.org/license/).

## Usage

To include it use: `__use(__namespace__, 'mysli/external/jquery');`.

```php
// jquery::get_link( string $version, boolean $development );
// To get production version 2.1.1 you can use
jquery::get_link('2.1.1', false);
// No parameters, will use values from configuration:
jquery::get_link();
```

### In template (tplp)

```html
::use mysli/external/jquery
<!DOCTYPE html>
<html>
<head>
    <!-- HTML script tag -->
    {|jquery/tag}
    <!-- Particular version -->
    {'1.9.1'|jquery/tag}
    <!-- Particular version, development -->
    {'2.1.1'|jquery/tag:true}
    <!-- URL only -->
    <script src="{|jquery/link}"></script>
    ...
```

## Config

| Key         | Default                                    | Description                               |
|-------------|--------------------------------------------|-------------------------------------------|
| remote_url  | http://code.jquery.com/jquery-{version}.js | Base URL from which jQuery will be loaded |
| version     | 2.1.1 | Default version (.min will be append) |
| dev_version | 2.1.1 | Development version                   |
| local       | false | Weather to load script from local source (will use cURL to acquire script from remote URL) |
| development | false | Weather development version should be used |

## License

The Mysli External jQuery is licensed under the GPL-3.0 or later.

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
