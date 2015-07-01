# Mysli DOT

## Introduction

The `dot` package provide command line functionality. It serves both as an
utility (used by an end user) and a library, used by developers.

## Usage as an Utility

If `dot` is installed in root `bin` directory, it will offer option for creation
of new applications. If it's run from application root, it will provide
functionalities related to that application (depends on which packages are
installed).

## Usage as an Library

Include available classes with:

`const __use = 'dot.{input, output, param, ui, util}'`

If you want to create new scrip for your package, put it into the `src/cli`
folder of your package.

Namespace must follow directory structure convention, e.g. script in folder
`vendor.package/src/cli/my_script.php` must contain a class:
`namespace vendor\package\cli; class my_script {}`.

The class should contain a static method `__run` which will get passed arguments.
