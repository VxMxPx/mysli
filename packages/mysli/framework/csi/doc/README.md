# Mysli Framework CSI

## Introduction

Common Setup Interface. Can be used to enable and configure libraries
through CLI or web interface.

## Usage

Include `csi`:

    __use(__namespace__, 'mysli/framework/csi');

Create new instance and add fields, in this example,
we'll use it in `mysli/cms/dash/setup`:

    static function enable() {
        $csi = new csi('mysli/cms/dash/enable');
        $csi
            ->text('Create a new user.')
            ->input(
                'username',
                'Username',
                'root@localhost',
                function (&$field) {
                    if (strpos($field['value'], '@') === false) {
                        $field['messages'] = 'Please enter a valid email address.';
                        return false;
                    }
                    return true;
                });
        if ($csi->status() !== 'success') {
            return $csi;
        }
        users::create([
            'email'    => $csi->get('username'),
            'password' => $csi->get('password'),
            'is_super' => true
        ]);
    }

## License

The Mysli Framework CSI is licensed under the GPL-3.0 or later.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
