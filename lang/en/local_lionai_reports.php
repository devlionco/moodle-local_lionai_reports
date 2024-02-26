<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_lionai_reports
 * @category    string
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'LionAI Reports';
$string['allreports'] = 'LionAI Reports';
$string['list'] = 'List';
$string['name'] = 'Name';
$string['id'] = 'ID';
$string['timecreated'] = 'Added';
$string['timemodified'] = 'Updated';
$string['sendprompt'] = 'Send prompt';
$string['getresult'] = 'Execute code and show preview';
$string['pickfromhistory'] = "Pick from report's history";
$string['examples'] = 'Examples';
$string['example1'] = 'Show all users that logged in yesterday, show just email and full name';
$string['example2'] = 'Show all unique quiz  in all courses , quiz name and course name and cmid must be shown';
$string['example3'] = 'Give me users whose first names contain the letter A and emails contain the digit 2';
$string['example4'] = 'Show all questions with type essay, that have unique name';
$string['example5'] = 'Get version from config of the plugin mod_quiz';
$string['trytofix'] = 'Try to fix';
$string['ctrlenter'] = 'You could use CTRL+Enter';
$string['lastmessages'] = 'Last messages';
$string['actions'] = 'Actions';
$string['deletewarning'] = '<span class=\'text-warning\'>Carefully! Deletes without confirmation</span>';
$string['delete'] = 'Delete';
$string['exportcrautowarning'] = 'Auto import to Configurable Reports';
$string['exportcrauto'] = 'Add to Configurable Reports';
$string['exportcrxmlwarning'] = 'Export XML file for using in Configurable';
$string['exportcrxml'] = 'Export XML file';
$string['exportsqlwarning'] = 'Export as SQL file format (.sql)';
$string['exportsql'] = 'Export SQL file';
$string['exportcsvwarning'] = 'Export report result in CSV format';
$string['exportcsv'] = 'Export CSV file';
$string['lionai_reports_apikey'] = 'API Key';
$string['lionai_reports_apikeyinfo'] = 'The free version allows you 10 prompt executions per week. To get the Pro version, contact us at <a href="mailto:info@devlion.co">info@devlion.co</a> ';
$string['lionai_reports_apiurl'] = 'API URL';
$string['lionai_reports_apiurlinfo'] = 'API URL';
$string['lionai_reports_limitrecords'] = 'Limit of records';
$string['lionai_reports_limitrecordsinfo'] = 'Choose the number of records that are displayed in the preview table. (Default is 10 records, Maximum is 500 records)';
$string['limited_to'] = '"LIMIT" keyword not allowed. Limited to {$a} records';
$string['permission_require'] = "You don't have permission, Only site admins can use this plugin.";
$string['not_eligible_message'] = 'Seems like your API key is wrong or you exceeded the 10 complimentary prompt executions per week in the free version. To get the Pro version, contact us at info@devlion.co';
$string['no_data_found'] = 'This query did not return any results';
$string['thumbupbtn'] = 'Like';
$string['thumbdownbtn'] = 'Dislike';
$string['only_select'] = 'Error - only "SELECT" queries allowed!';
$string['privacy:metadata:local_lionai_reports'] = 'Information about every report, including the user who saved the report and the history of prompts and queries.';
$string['privacy:metadata:local_lionai_reports:userid'] = 'The ID of the user that saved this report.';
$string['privacy:metadata:local_lionai_reports:options'] = 'The history of the queries and prompts that the user had used in this report.';
$string['preview'] = 'Preview';
