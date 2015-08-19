# Config

Used to access and create configuration for packages.

## Usage

Example, create configuration for a package:

    $options = [
        'an_example' => ['string', 'example_value']
    ];
    $config = config::select('vendor.package');
    $config->init($options);
    $config->save();

When initializing new options, as in example above, each value must be
an array, of which first element must be type, and second must be actual value.
When later a value is changed (set) type is not needed, but value needs
to be as it was specified when configuration was initialized.

Configuration needs to be initialized only once,
usually when package is enabled.

Access configuration:

    $config = config::select('vendor.package');
    $config->get('an_example'); // example_value

Or statically:

    $example_value = config::select('vendor.package', 'an_example');

Setting a value:

    $c = config::select('vendor.package')
    $c->set('example_value', true);

Adding a new configuration key:

    $c = config::select('vendor.package')
    $c->add('example_value', 'boolean', true);

To remove all configurations for a particular package `destroy` method
can be used:

    config::select('vendor.package')->destroy();

NOTE: This class is singleton, only one instance is allowed per PACKAGE.
Constructor is hence private, and config::select() must be used,
to get a (new) instance of configuration.
