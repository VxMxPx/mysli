#!/bin/bash

set -ev

# Install the system
php ./packages/mysli/installer/src/php/stub.php -y

# To private
cd private

./dot pkgm -e mysli.dev.phpt

# Run tests...
./dot phpt -t mysli.dev.phpt

./dot phpt -t mysli.framework.cli
./dot phpt -t mysli.framework.type
./dot phpt -t mysli.framework.ym

./dot phpt -t mysli.util.datetime
./dot phpt -t mysli.util.i18n
./dot phpt -t mysli.util.tplp
