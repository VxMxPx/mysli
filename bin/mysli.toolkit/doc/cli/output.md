# Output

Used to output text (in various styles) to the console.

Output handles general outputs to the command line interface.
Output supports basic formatting, like colors, bold and aligned text.

There's also an UI class, which of aim is to provided a consistent command line
interface amount various packages.

## Usage

To print a single line of a regular text, `line` method can be used:

    output::line('Hello world!');

To fill full width of terminal window with a particular character `fill` method
is available:

    output::fill('-');

To format a string (change text color, background color,...), there's a `format`
method:

    output::format("Today is a <bold>%s</bold> day!", ['nice']);


Tags need not to be closed. To close all opened tags </all> can be used.

Available tags are:

Formating: bold, dim, underline, blink, invert, hidden

Text color: default, black, red, green, yellow, blue, magenta, cyan, light_gray,
dark_gray, light_red, light_green, light_yellow, light_blue, light_magenta,
light_cyan, white

Background color: bg_default, bg_black, bg_red, bg_green, bg_yellow, bg_blue,
bg_magenta, bg_cyan, bg_light_gray, bg_dark_gray, bg_light_red, bg_light_green,
bg_light_yellow, bg_light_blue, bg_light_magenta, bg_light_cyan, bg_white

There are shortcut methods available for each tag:

    output::red('Red text!');
    output::green('Green text!');

Please use `ui` class for semantic output, e.g. to output text by particular
role, as: title, list, error, ...

