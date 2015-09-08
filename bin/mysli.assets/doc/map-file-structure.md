# Assets Map file

Map file has two functions:

1. it servers as an instruction for how to build assets,
2. it's used for inserting those assets into an actual page.

Bellow is the example of a map file:

```yaml
id: default:assets

tags:
    css:
        match: [ css ]
        tag: '<link rel="stylesheet" type="text/css" href="{link}">'
    js:
        match: [ js ]
        tag: '<script type="text/javascript" src="{link}"></script>'
    img:
        match: [ jpg, png, gif ]
        tag: '<img src="{link}" />'

process:
    styl:
        match: *.styl
        require:
            - stylus --version | grep ^0.*$
        process:
            - stylus {src/file} -o {dest/file.css}
            - {dest/file.css}
        compress:
            - {css/compress}
    css:
        match: *.css
        require:
            - cleancss --version | grep ^3.*$
        process:
            - cp {src/file} {dest/file}
            - {dest/file}
        compress:
            - cleancss {dest/file.css} -o {dest/file.min.css}
            - {dest/file.min.css}
    js:
        match: *.js
        require:
            - uglifyjs --version | grep "^uglifyjs 2.*$"
        process:
            - cp {src/file} {dest/file}
            - {dest/file}
        compress:
            - uglifyjs -c -o {dest/file.min.js} {dest/file} --screw-ie8
            - {dest/file.min.js}
    tplp:
        match: *.tplp.html
        require:
            - mysli --self version | grep "^Mysli .*$"
            - mysli tplp --version | grep "^Mysli .*$"
        process:
            - myli tplp -f {src/file} {dest/}
            - {dest/file.php}
    i18n:
        match: *.lng
        require:
            - mysli --self version | grep "^Mysli .*$"
            - mysli i18n --version | grep "^Mysli .*$"
        process:
            - mysli i18n -f {src/file} {dest/}
            - {dest/file.json}
    copy:
        match: *.*
        require:
            - cp --version | grep "^cp .*$"
        process:
            - cp -r {src/file} {dest/file}
            - {dest/file}
    svg:
        match: *.svg
        require:
            - inkscape --version | grep "^Inkscape 0.9.*$"
            - convert --version | grep "^Version: ImageMagick 6.9.*$"
        process:
            - inkscape -z -f {src/file} -e {dest/file.png}
            - convert -quality 90 {dest/file.png} {dest/file.jpg}
            - rm {dest/file.png}
            - {dest/file.jpg}

files:
    css:
        process: [ styl, css ]
        merge: mrg-main.min.css
        include:
            - toolbar.css
            - button.css
    js:
        process: [ js ]
        merge: mrg-main.min.js
        include:
            - button.js
            - widget.js
    common:
        process: [ svg, copy ]
        exclude: [ externals/, all.zip ]
        include: *.*
    tplp:
        process: [ tplp ]
        publish: No
        include: *.tplp
    i18n:
        process: [ i18n ]
        publish: No
        include: *.lng
```

This is not as complex as it seems. The above example is demonstrating
all possible usages for how assets building works. In most cases map files
will be simpler.

## Explanation of keys

### ID

Id can be entirely omitted if you're using assets in package. In case of packages
ID will be automatically assigned (to be `package:vendor.name`). ID is mostly
used for themes, in such case convention is to use `theme:theme_name` as an
ID. ID is used later in template to access assets, for example:
`{ 'css' | assets.tag : 'theme:theme_name' }` would grab `css`
assets for ID `theme:theme_name`.

### TAGS

Tags are used in template, when using: `assets.tag` to output tag(s) for
particular assets. This section can be omitted as `css`, `javascript` and `img`
tags are already defined in default settings.

You can use it to define your own tags, or to overwrite already defined tags.

### PROCESS

Process section is used to process various file types.
This is simply running various command line utilities on individual files.
Couple of variables are available:

```
{src/file}  -- Source full path + filename.
{src/}      -- Source directory.
{dest/file} -- Destination full path + filename, this will use default
               destination path with the same filename as a source.
{dest/}     -- Destination directory.
```

For both `{src/file}` and `{dest/file}` `.ext` can be used, for example, `{des/file.new}`
will modify extension of a file to be `.new`. Actual example of this:

For this example, source filename (`{src/file}`) is `/home/www/post.txt`,
the default destination is set to be `dist~`.

```
cp {src/file} {dest/file.md}
```

The result of above command will be `/home/www/dist~/post.md`, note
the extension change to `md`.

**match** _required_

Define file type to be matched by this processor. For example: `*.styl` will
match all files with `styl` extension. Use an array for multiple extensions:
`[ *.styl, *.css ]`.

**require**

An optional list of required modules by this processor. This is simply a list
of commands, each of them should return `true` when executed.

Use `grep` to match pattern or particular version if that's important.

**process**

List of commands to process a file. If you don't want to process a file, you
can use just `cp` command to copy it.

IMPORTANT: the last command on the list must be new (produced) filename.

**compress**

Same as `process`, but used for compressing of processed file.
This can be entirely omitted if no compression is needed.

Alternatively, other sections can be referenced, use {id/action}.
For example, if two `css` processors are using same method of compressing,
then they can both reference 3rd party compressor:

```
process:
    css-compressor:
        require:
            - cleancss --version | grep ^3.*$
        compress:
            - cleancss {dest/file.css} -o {dest/file.min.css}
            - {dest/file.min.css}
    less:
        match: *.less
        require: ...
        process: ...
        compress:
            - {css-compressor/compress}
    styl:
        match: *.styl
        require: ...
        process: ...
        compress:
            - {css-compressor/compress}
```

IMPORTANT: the last command on the list must be new (compressed) filename.

### FILES

Files section is used when processing and when inserting tags in template.
This section is required, it will tell how particular files and folders should
be processed.

Each key represent a directory, relative to the `map.ym` file. So a `css` key
will look for a `css` directory.

Keys are later used to acquire assets in template, for example:
`{ 'css' | assets.tag | 'vendor.package' }` will print all `css` tags. This is
a bit more complex, as in case of `merge` only one tag (merged file) will be
inserted if not in a debug mode.

**process** _required_

List of processors to be used on this directory, for example, if we have a
`css` directory, which contains `css` and `styl` files, we'd use `[ styl, css ]`
processors. Processors are defined in a `process` section of a map file.

If you'd like to simply copy files, use `copy` processor (predefined).

**merge** _[ False ]_

Weather all included files should be merged into one single file. This can be
useful when having multiple `css` or `js` files which should be merged into
one file for production. The default is false, expected value type is string,
an actual filename of merged assets.

**publish** _[ True ]_

Weather this directory (all included files) should be publicly (URL) accessible.

**include** _[ \*.\* ]_

List of files to be included. This can be a list of individual files or a filter
in format `*.ext`. Files will be included in order specified. If merge is not
false, files will be merged in specified order.

**exclude**

List of files to be excluded. Use end slash `/` to exclude whole directory, e.g.
`.git/`.
