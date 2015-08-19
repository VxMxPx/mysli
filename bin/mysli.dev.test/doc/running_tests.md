# Running Tests

Tests can be run through command line. Use `mysli test --help` for list of
available commands.

### Examples.

Run all tests for package:

    mysli test mysli.toolkit

Run tests for one class:

    mysli test mysli.toolkit.router

Run tests for a method:

    mysli test mysli.toolkit.router::add

If tests for a method are spread across multiple files, just use a full test
filename (with no extension):

    mysli test mysli.toolkit.router::add.special
