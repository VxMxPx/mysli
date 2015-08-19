# YM

YM is a simplified YAML parser. Some features of original YAML are
deliberately not implemented hence package name is YM, and filenames are `.ym`.

This version is aimed for speed and to be used for the toolkit's
configuration files. It's still slower than native JSON extension, but files
are more readable and less error prone.

The `.ym` files can be parsed with native YAML extension, but `.yaml` files
might not be parsed successfully with this class.

## Usage

Standard methods are available: `decode`, `decode_file`,
`encode` and `encode_file`.

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

... or associative:

    items:
        key  : value
        key2 : value

... nested:

    level1:
        level2:
            - one
            - two

Inline array:

    fruits: [ banana, pineapple, pear, orange, strawberry ]

Inline array can be associative:

    fruits: [ banana: 2, pineapple: 1, pear: 23, orange: 4, strawberry: 120 ]

Quotes can be used to make item string rather than associative:

    fruits: [ "banana: 2", pineapple: 1, ... ]

Nested inline arrays are supported:

    fruits: [ yellow: [ banana, lemon ], red: [ strawberry, cherry ] ]

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

... to start key with a hash, a double quotes can be used:

    "#hash_key" : value

