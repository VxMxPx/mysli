# Assets Map file

Map file has two functions:

1. it servers as an instruction for how to build assets,
2. it's used for inserting those assets into an actual page.

Please see `mysli.assets/config/defaults.ym` for an examples.

Bellow is an example of the file itself.

```yaml
tags:
    css:
        match: [ css ]
        tag: '<link rel="stylesheet" type="text/css" href="{link}">'

modules:
    styl:
        produce: css
        require: stylus --version | grep ^0.*$
        process: stylus {in/file} -o {out/file}
        build:   $css.build
    css:
        produce: css
        require: cleancss --version | grep ^3.*$
        process: cp {in/file} {out/file}
        build:   cleancss {in/file} -o {out/file}

includes:
    css:
        modules: []
        ignore: No
        process: Yes
        publish: Yes
        merge: css.min.css
        files:
            - variables.css !reload
            - "*.css"
```

This is not as complex as it seems. The above example is demonstrating
all possible usages for how assets building works. In most cases map files
will be simpler.

## Explanation of keys

### tags

Tags are used in template, when using: `assets.tags` to output tag(s) for
particular assets. This section can be omitted as `css`, `javascript` and `img`
tags are already defined in default settings.

You can use it to define your own tags, or to overwrite already defined tags.

Tag section includes two items: `match` which is an array and `tag`.

Match is simply as list of all extensions, for example, for image (img) tag,
`match` could be: `[ jpg, jpeg, png, gif ]`.

Tag is an actual tag, for example, for image (img) it could be:
`<img src="{link}" alt="" />`. There's one variable called `{link}` which
will be replaced with an actual file URL.

### modules

Modules section contains instruction of how to process particular file type.
The main key of an individual module is a file extension.

Following variables can be used in modules:

```
{in/file}  -- Input file, full path + filename.
{in/}      -- Input directory.
{out/file} -- Output file, full path + filename, this will use default
              destination path with the same filename as a source.
{out/}     -- Destination directory.
```

**produce** _required_

What's the final output file format (extension).

**require**

An optional list of required modules. This is simply a list
of commands, each of them should return `true` when executed.

Use `grep` to match pattern or particular version if that's important.

**process**

List of commands to process a file. If you don't want to process a file, you
can use just `cp` command to copy it.

**build**

Build will be used when assets are run, not in development mode.
It's usually used to compressed files.

### includes

Files section is used when processing and when inserting tags in template.
This section is required, it will tell how particular files and folders should
be processed.

Each key represent a directory, relative to the `map.ym` file. So a `css` key
will look for a `css` directory.

Keys are later used to acquire assets in template, for example:
`{ 'css' | assets.tags | 'vendor.package' }` will print all `css` tags. This is
a bit more complex, as in case of `merge` only one tag (merged file) will be
inserted if not in a debug mode.

**modules** _[ [] ]_

List of specific modules rewrites (or add-ons) for this particular directory.
It will allow to set specific instructions which will apply only to this
directory.

**ignore** _[ False ]_

Weather to ignore this directory completely. If set to true, directory will be
completely omitted by assets all together.

**process** _[ True ]_

Weather to process the directory (using above defined modules). In some cases,
processing is not required, for example, there might be a directory containing
images which needs to be only published without any changes to its content.

**publish** _[ True ]_

Weather the directory (all included files) should be publicly (URL) accessible.

**merge** _[ False ]_

Weather all included files should be merged into a single file. This can be
useful when having multiple `css` or `js` files which should be merged into
one file for production. The default is false, expected value type is string,
an actual filename of merged assets.

**files** _required_

List of files to be processed. This can be actual list of individual files,
a wildcard pattern (e.g. *.css) or a combination of both.

Files can have a special instructions attached, for example `- master.css !reload`.
In case of `!reload`, upon changes in this specific file, all files on the
list will be reloaded. This is useful when having a file containing variables
used in other files.
