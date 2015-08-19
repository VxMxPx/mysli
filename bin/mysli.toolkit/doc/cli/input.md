# Input

The Input class is used to gather user's input from command line.

## Usage

To grab single input line, `line` method can be used:

    $input = input::line('Enter you text: ');

This method will accept second parameter, a function, which will run as long as
`null` is being returned:

    list($uname, $domain) = input::line('Enter an email: ', function ($input)
    {
        if (strpos($input, '@') && strpos($input, '.'))
        {
            return explode('@', $input, 2);
        }
        else
        {
            echo "Invalid e-mail address, try again.\n"
            return;
        }
    });

Additional to `line`, there are also `multiline` and `password`
methods. They accept the same arguments, the difference being,
`multiline` will terminate on two new lines, and password will hide input.

Finally `confirm` is available which will print `y/n` to the user, and return
boolean value.

    // Second parameter (boolean) will set default value.
    $answer = input::confirm('Are you sure?', false);
