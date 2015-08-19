# Signature

Allows you to sign string.

## Usage

Create signature on a string with `create` method"

     $signed = signature::create($string, $key);

Validate signature:

     signature::is_valid($signed, $key);

Check weather a string is signed at all:

     signature::has($signed);

Strip a string signature:

     $string = signature::strip($signed);
