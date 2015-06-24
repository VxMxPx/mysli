# Mysli Framework Ym

## Introduction

Mysli Ym is a simplified YAML parser.

## Usage

You can import `ym` with: `__use(__namespace__, 'mysli/framework/ym')`

To parse a file use:

    ym::decode_file($absolute_file_path);

To parse a (ym) string use:

    ym::decode($ym);

## Supported Syntax

String value:

    key : string

... or explicit string:

    key : "string"

Boolean:

    one   : Yes
    two   : No
    three : True
    four  : False

Integer and float:

    im_integer : 12
    im_float   : 12.2

Array:

    items:
        - item one
        - item two
        - item three

... or associative:

    items:
        key  : value
        key2 : value

... nested:

    level1:
        level2:
            level3:
                - one
                - two
    -
        -
            - one
            - two
            -
                key : value

Comments:

Comments must start with hash (`#`) which can be indented...

    key : value
    # Comment!
    array:
        # Comment!
        - one
        - two

... but cannot be inline:

    key : value # Inline comment, considered part of a value!

... to start key with hash, you can use double quotes:

    "#hash_key" : value

## License

The Mysli Framework Ym is licensed under the GPL-3.0 or later.

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
