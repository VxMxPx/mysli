# Mysli Installer

This package will help you setup Mysli system base. There are two ways to run
the installer. Either through your browser or command line.

## Browser

Copy this package to the public path of your web server, and rename it to 
`installer.php` (the important part is extension, which must be renamed from
`.phar` to `.php`).

Open the browser and visit your web server. Follow the instructions written on
screen.

## Command Line

Run installer with `php mysli.installer-r<current release>.phar` 
(replace `<current release>` with actual release number), follow instructions on
screen.

### Available options

You can use `-h` to see list of available options.

`-p, --pkgpath <name>` Packages's path. The default is: `*packages`
This will try to automatically discover packages path. If you want to enter 
costume path, you can enter a relative path (example: ../../packages) or 
a full absolute path. You can also use `*folder_name` to find folder 
automatically relative to the current location.

`-d, --datpath <name>` Data / private path (where configuration and databases
will be stored. Should not be URL accessible) The default is: 
`{pkgpath}/../private`.

`-r, --replace <options>` Replace core packages in format: 
`role:vendor.package,...`. The default values are:
    
- core: mysli.framework.core
- cli:  mysli.framework.cli
- pkgm: mysli.framework.pkgm

This will try to locate `.phar` package automatically and use first match. 
If there's no `.phar` package, it will try to use source if exists.

You can force exact version of package by providing full package's name 
(including .phar extension), for example: `mysli.framework.core-r150229.1.phar`.

If you want to force use of source, then provide package's name in path format 
(replace `/` with `.`), for example: 
`mysli/framework/core` rather than `mysli.framework.core`.

`-y` Assume `yes` as an answer to all questions.

`-h, --help` Print help.


## License

The Mysli Installer is licensed under the GPL-3.0 or later.

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
