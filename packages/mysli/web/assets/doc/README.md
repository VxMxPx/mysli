# Mysli Web Assets

## Introduction

Help you to merge and compress assets thought command line and to
load them in templates.

## Usage

Create `assets` folder in root directory of your package and put `map.ym`
(or .json) into it.

### Map file

Map file contains instructions how to build assets for your package.
The structure of file looks like this (.ym format):

```yaml
# Additional settings, how to parse/compress various file types,
# mostly you don't need to include this section.
# By default `cleancss`, `stylus`, `uglifyjs` and `less` modules are configured.
settings:
    # Specify which npm modules are required by this package
    require:
        # In this case less(c) is required
        lessc:
            # The command to be executed, to check if required module is installed.
            # In this case, {id} will be replaced with `lessc` (as defined above)
            command : {id} --version
            # What is expected result of above command
            # `*` will match any character.
            expect  : lessc 1.*.* *
            # Level: either `error` or `warn`.
            # If error, then the build will be terminated if above command
            # doesn't match expected results.
            # If warn, then only message will be displayed, but build will continue.
            level   : error
            # Message to be displayed if command doesn't match expected results.
            # Supported variables are: {id}, {expect} and {result}
            message : "Missing `{id}` (npm install -g less) version `{expect}` got `{result}`"
    # Specify which command should be used to compress various file types
    compress:
        # In this example, `cleancss` is used.
        # Supported variables are:
        # {source}, {dest}, {source_dir}, {dest_dir}, {web}
        css : cleancss {source} -o {dest}
    # Specify how various file types should be processed
    process:
        # For `less` file extensions, `lessc` command will be used
        less : lessc {source} > {dest}
    # Specify extension transformation...
    ext:
        # ... in this example, `less` will become `css`
        less : css
# Command to be executed before build (only once if -w)
before:
    - command var {source}, {dest}, {source_dir}, {dest_dir}, {web}
    - another command
# Files to be produces, this section is required
files:
    # This will be main CSS file for particular package. In template it could
    # be included with: {'vendor/package/css/main.css'|assets/tags:'css'}
    css/main.css:
        # ... weather file should be compressed
        compress : Yes
        # ... the file will consist of following files, in specified order
        include  :
            - css/fonts.css
            - stylus/layout.styl
            - stylus/elements.styl
    # The same as example above.
    js/main.js
        compress : Yes
        include  :
            - js/elements.js
            - js/other.js
    # There can be as many files as you want, and they can share includes.
```

By default `cleancss`, `stylus`, `uglifyjs` and `less` npm modules are required.
See `data/defaults.ym` for details.

### Command line

Use `./dot assets --help` to see available commands, basic usage example:

```
./dot assets -b vendor/package
```

... or to rebuild when changes occurs:

```
./dot assets -wb vendor/package
```

You can change default paths, either through command line (see help), so to
define new segment in `mysli.pkg.ym`, with following content:

```yaml
assets:
    source      : assets
    destination : _dist/assets
    map         : map.ym
```

### Templates

If you're using `tplp` then you can request _assets_
with `use mysli/web/assets`. After that you can print css or javascript tags
anywhere in your template with:

```
// CSS
{'vendor/package/css/main.css'|assets/tags}
// JS
{'vendor/package/js/main.js'|assets/tags}
```

## Config

| Key    | Default | Description                                                 |
|--------|---------|-------------------------------------------------------------|
| debug  | false   | If true, the non-minified versions of files will be served. |

## License

The Mysli Web Assets is licensed under the GPL-3.0 or later.

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
