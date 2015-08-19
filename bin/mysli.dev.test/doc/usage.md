# Usage

Create a new `tests` directory in a root of your package. Create a new folder
for each class you'd like to test. For example, an `mysli\toolkit\lib\router`
class would get `router` folder.

Tests files must have `t.php` extension. Usually there's one file corresponding
to one method, containing multiple tests. But in some cases, tests for one
method can be spread across multiple files. In such case is a good practice
to append one word description to the file name, for example:
`process.comments.t.php`; in this example, `process` is a method name, `comments`
is a short description.

For sub-classes (e.g. mysli\toolkit\fs\file), create additional directory for
a namespace (`fs`), so a test file would look like this:
`mysli\toolkit\tests\fs\file\read.t.php`.

Full representation of test filename and path in one line:
`vendor/package/tests/class/[namespace/]method.[description.].t.php`.
