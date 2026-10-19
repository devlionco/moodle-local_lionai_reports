# LionAI Reports #

Turn plain-language questions into Moodle SQL reports. Site administrators describe the report they need, an AI service
generates a SQL **SELECT** query, and the plugin lets you preview, edit, rate and export the result.

## Features ##

* Write a prompt in natural language (at least 2 words) and get a SQL query back.
* Preview the result in a table and edit the generated SQL before running it.
* Keep a history of prompts and queries per report, and pick from it later.
* Export a report as CSV, as SQL, as a Configurable Reports XML file, or add it directly to
  [Configurable Reports](https://moodle.org/plugins/block_configurable_reports) (when that block is installed).
* Rate generated queries (thumbs up/down) to improve results.
* Example prompts to get started.

## Requirements ##

| Component | Version |
|-----------|---------|
| Moodle    | 5.2 (branch `MOODLE_502_STABLE`) |
| PHP       | 8.2 or higher (as required by Moodle 5.2) |
| Database  | MySQL-based (MySQL, MariaDB) - the generated queries are SQL |

Older Moodle versions (4.1 - 4.3) are supported by the `main` branch.

## Installation ##

### Via uploaded ZIP file ###

1. Log in as an admin and go to _Site administration > Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code.
3. Check the plugin validation report and finish the installation.

### Manually or with git ###

Put the plugin in

    {your/moodle/dirroot}/public/local/lionai_reports

(on Moodle 4.x use `{your/moodle/dirroot}/local/lionai_reports`), for example:

    $ git clone -b MOODLE_502_STABLE <repository URL> public/local/lionai_reports

Then log in as an admin and go to _Site administration > Notifications_, or run:

    $ php admin/cli/upgrade.php

## Configuration ##

Go to _Site administration > Plugins > Local plugins > LionAI Reports_:

| Setting | Description |
|---------|-------------|
| API key | Your LionAI API key. **Required** - no key is provided by default. Contact info@devlion.co to get one. |
| API URL | The LionAI service endpoint. |
| Restrict to own reports | Limits non-admin listing to the reports of the current user. |
| Preview limit | Number of rows shown in the preview (maximum 500). |
| Export row limit | Maximum rows in a CSV export (default 100000). Exports are streamed row by row. |

## Usage ##

Open _Site administration > Reports > LionAI Reports_, press **Add New Report**, type your request in the prompt box
(for example: "Show all unique quizzes in all courses, with quiz name, course name and cmid") and press **Send prompt**
(or Ctrl+Enter). Review or edit the SQL, press **Get result** to preview it, then export it.

## Security and privacy ##

* Only site administrators can use the plugin.
* Only a single read-only `SELECT` (or `WITH ... SELECT`) statement is executed; `UPDATE`, `INSERT`, `DROP` and any other
  statement that changes the database are rejected. For extra safety, consider running Moodle with a read-only DB user
  for reporting.
* **Data sent to a third party:** the text of your prompt, the database type and the Moodle version are sent to the
  configured LionAI API URL. No database content is sent. Do not put personal data in prompts.
* The plugin implements the Moodle Privacy API; prompt history is removed when a user's data is deleted.

## Plans ##

The free version allows 10 prompt executions per week. To get the Pro version, contact info@devlion.co.

## Technical Support ##

If you have questions or need help integrating LionAI Reports, please contact us (support@devlion.co) instead of opening an issue.

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
