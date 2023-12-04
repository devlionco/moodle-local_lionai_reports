# LionAI Reports #

## Introduction ##
LionAI Reports simplifies report generation in Moodle by enabling administrators to effortlessly write natural language queries, instantly converting them into SQL queries. Preview, edit, and export reports in various formats for seamless data analysis.

LionAI Reports free version allows 10 prompt executions per week. To get the Pro version, contact us at info@devlion.co

## Requirements ##

In order to use LionAI Reports you need to have one of the following:
1. Moodle version 4.1
2. Moodle version 4.2

Moreover, the plugin requires PHP version of 8.1 and above.

## Installation ##
### Installing via uploaded ZIP file ###

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

### Installing manually ###

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/lionai_reports

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

### Installing with git ###

Use
    $ git clone { this repository URL/git address}

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Technical Support ##
If you have questions or need help integrating LionAI Reports, please contact us (Support@devlion.co) instead of opening an issue


## License ##

2023 Devlion <info@devlion.co>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
