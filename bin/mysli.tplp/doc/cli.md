# Tplp CLI

Use `mysli template vendor.package` to pre-build all templates. Templates will
be saved to `assets/tplp/~dist`.

Files prefixed with underline e.g.: `_layout.tpl.html` will be excluded from parsing.
You should prefix layouts and modules -- those will be parsed as they're
imported by other file.
