# Usage

Pack utility offers command line interface. Run `mysli pack --help` for
a list of options.

An example of producing Toolkit's package:

    mysli pack mysli.toolkit

Pack utility will be run in an interactive mode, it will ask you weather you
want to increase the main version, and to enter a costume release.
You can use `-y` to assume _yes_ as an answer to all questions.

Produces package will be placed into `~releases` folder in a rood directory of
a package.

The following files and folders will not be included in a newly produced package:

    - doc/COPYING
    - tests/
    - ~releases/

To add more files and folders to the ignore list, add to `mysli.pkg.ym`:

    pack:
        ignore:
            - file/to/ignore.txt
            - folder/to/ignore/
