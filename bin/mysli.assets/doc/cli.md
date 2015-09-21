# Assets Command line

Command line is used to build assets, it's used strictly for development, end
users should always receive pre-build assets.

Use `mysli assets --help` to see available commands, basic usage example:

```
mysli assets vendor.package
```

... or to rebuild when changes occurs:

```
mysli assets -w vendor.package
```

Default paths can be changed, either through command line (see help), or
by defining a new segment in `mysli.pkg.ym`, with following content:

```yaml
assets:
    path: new/path
    map: map.ym
```
