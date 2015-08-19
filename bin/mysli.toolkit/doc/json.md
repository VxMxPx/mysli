# Json

A very simple JSON manipulation class, depends on native functions.

## Usage

Use `encode` to encode a JSON string, and `decode` to decode it.
Additional to that, methods which will read/write directly from file
are available:

    json::decode_file($filename, true);

On error `mysli\toolkit\exception\json` will be thrown.

## See Also

[JSON Functions on PHP.net](https://secure.php.net/manual/en/ref.json.php)
