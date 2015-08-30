# Mysli Assets

## Introduction

Merge and compress assets thought command line and load them in templates.

## Usage

Create `assets` folder in root directory of your package and put `map.ym` into it.

### Map file

Map file contains instructions how to build assets for a package or a theme.
The structure of file looks like this (.ym format):

```yaml
# Specify which npm modules are required by package.
# Modules are used in processing/compressing of assets.
# By default `cleancss`, `stylus`, and `uglifyjs` modules are configured.
require:
    # In this example less(c) is required
    lessc:
        # The command to be executed, to check if required module is installed.
        # In this case, {id} will be replaced with `lessc` (as defined above)
        command: {id} --version
        # What is expected result of above command `*` will match any character.
        expect: lessc 1.*.* *
        # Level: either `error` or `warn`.
        # If error, then the build will be terminated if above command doesn't
        # match expected results.
        # If warn, then only message will be displayed, but build will continue.
        level: error
        # Message to be displayed if command doesn't match expected results.
        # Supported variables are: {id}, {expect} and {result}
        message: >
            Missing `{id}` (npm install -g less)
            version `{expect}` got `{result}`.

# Specify how to process various extentions.
# Following variables are supported:
# {src/file} -- Source file (full path).
# {dest/file} - Destination file (full path).
# If extention is included, it will be modified, e.g.:
#   {src/file} > {dest/file.css}
#   If file is `button.less` it will be changed to `button.css`.
process:
    less:
        process: lessc {src/file} > {dest/file.css}
        compress: cleancss {dest/file.css} -o {dest/file.min.css}

# Command to be executed before build.
before:
    - command var {src}, {dest}
    - another command

# Files to be produces, this section is required
files:
    # This will be main CSS file for particular package.
    css/main.css:
        # ... weather file should be compressed
        compress: Yes
        # ... the file will consist of following files, in specified order
        include:
            - css/fonts.css
            - stylus/layout.styl
            - stylus/elements.styl
    # The same as example above.
    js/main.js
        compress: Yes
        include:
            - js/elements.js
            - js/other.js
    # There's no limit of files, they can share includes.
```

By default `cleancss`, `stylus`, `uglifyjs` and `less` npm modules are configured.
See `config/defaults.ym` in a root of assets package for details.

### Command line

Use `./dot assets --help` to see available commands, basic usage example:

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

### Templates

If you're using `tplp` then you can request _assets_
with `use mysli.assets`. After that you can print css or javascript tags
anywhere in your template with:

```
// CSS
{'vendor.package/css/main.css'|assets/tags}
// JS
{'vendor.package/js/main.js'|assets/tags}
```

## Config

| Key    | Default | Description                                                 |
|--------|---------|-------------------------------------------------------------|
| debug  | false   | If true, the non-minified versions of files will be served. |
