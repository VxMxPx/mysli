# Mysli Util Curl

## Introduction

This is a simple cURL wrapper. Beside wrapping native functions in class, it
offers some static _shortcuts_, for post, get and cookies.

## Usage

To include it use: `__use(__namespace__, 'mysli/util/curl');`.

Basic usage:

```php
$curl = new curl($url);
$curl->set_opt(CURLOPT_HEADER, false);
$curl->exec();
```

... post:

```php
curl::post($url, $data);
```

... get, with cookie:

```php
curl::get(curl::with_cookie($url));
```

... please note that, for precise configuration, you can combine above static
methods with instantiated class:

```php
$curl = new curl($url);
$curl->set_opt(CURLOPT_CONNECTTIMEOUT, 60);
curl::with_cookie($curl);
curl::post($curl, $data)->exec();
```

## Config

* `user_agent` [true]
    - Weather to acquire an agent from user, rather than using costume
* `costume_agent` [Mozilla/5.0 (X11; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0]
    - If user's agent's not set, or set to false, this will be used as a fallback
* `cookie_filename` [default.txt]
    - Cookie will be stored in {datpath}/{cookie_filename}
* `default`
    - Defaults:
        + `CURLOPT_FOLLOWLOCATION: true`
        + `CURLOPT_ENCODING : ''`
        + `CURLOPT_AUTOREFERER : true`
        + `CURLOPT_CONNECTTIMEOUT : 8`
        + `CURLOPT_TIMEOUT : 8`
        + `CURLOPT_MAXREDIRS : 8`
    - cURL overwrites, be very careful with those. Only applied when calling ::post, ::get

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
