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

$string['actions'] = 'Actions';
$string['addnewreport'] = 'Add New Report';
$string['addprompt'] = 'Add prompt: ';
$string['aitokennotice'] = 'This plugin must be configured with an <strong>AI token</strong> (API key) in order to work. To receive a token and use the trial version, write to <a href="mailto:info@devlion.co">info@devlion.co</a>.';
$string['allreports'] = 'LionAI Reports';
$string['creator'] = 'Creator';
$string['ctrlenter'] = 'You could use CTRL+Enter';
$string['curlerror'] = 'Could not connect to the service ({$a}). Please contact info@devlion.co to resolve the problem.';
$string['delete'] = 'Delete';
$string['deleteconfirm'] = 'Are you sure you want to delete the report "{$a}"? This cannot be undone.';
$string['deletewarning'] = 'Delete report';
$string['editname'] = 'Edit name';
$string['example1'] = 'Show all users that logged in yesterday, show just email and full name';
$string['example2'] = 'Show all unique quiz  in all courses , quiz name and course name and cmid must be shown';
$string['example3'] = 'Give me users whose first names contain the letter A and emails contain the digit 2';
$string['example4'] = 'Show all questions with type essay, that have unique name';
$string['example5'] = 'Get version from config of the plugin mod_quiz';
$string['examples'] = 'Examples';
$string['exportcrauto'] = 'Add to Configurable Reports';
$string['exportcrautowarning'] = 'Auto import to Configurable Reports';
$string['exportcrxml'] = 'Export XML file';
$string['exportcrxmlwarning'] = 'Export XML file for using in Configurable';
$string['exportcsv'] = 'Export CSV file';
$string['exportcsvwarning'] = 'Export report result in CSV format';
$string['exportsql'] = 'Export SQL file';
$string['exportsqlwarning'] = 'Export as SQL file format (.sql)';
$string['getresult'] = 'Execute code and show preview';
$string['id'] = 'ID';
$string['invalidapikey'] = 'Your API key is not valid, please contact info@devlion.co to resolve the problem.';
$string['invalidapiresponse'] = 'Invalid response received from the LionAI service. Please contact info@devlion.co to resolve the problem.';
$string['invalidapiurl'] = 'Your API URL is not valid, please contact info@devlion.co to resolve the problem.';
$string['lastmessages'] = 'Last messages';
$string['limited_to'] = '"LIMIT" keyword not allowed. Limited to {$a} records';
$string['lionai_reports:manage'] = 'Manage LionAI Reports';
$string['lionai_reports_allsee'] = 'Reports seen by everyone';
$string['lionai_reports_allsee_info'] = "Allow all report creators to see reports created by other creators on this site";
$string['lionai_reports_apikey'] = 'API Key';
$string['lionai_reports_apikeyinfo'] = 'The free version allows you 10 prompt executions per week. To get the Pro version, contact us at <a href="mailto:info@devlion.co">info@devlion.co</a> ';
$string['lionai_reports_apiurl'] = 'API URL';
$string['lionai_reports_apiurlinfo'] = 'API URL';
$string['lionai_reports_exportlimit'] = 'Export row limit';
$string['lionai_reports_exportlimitinfo'] = 'Maximum number of rows written to a CSV export. Rows are streamed, so a high value does not use much memory, but very large exports can be slow.';
$string['lionai_reports_limitrecords'] = 'Limit of records';
$string['lionai_reports_limitrecordsinfo'] = 'Choose the number of records that are displayed in the preview table. (Default is 10 records, Maximum is 500 records)';
$string['list'] = 'List';
$string['name'] = 'Name';
$string['newnameforreport'] = 'New name for report {$a}';
$string['newnameforreportshort'] = 'New name for report';
$string['no_data_found'] = 'This query did not return any results';
$string['not_eligible_message'] = 'Seems like your API key is wrong or you exceeded the 10 complimentary prompt executions per week in the free version. To get the Pro version, contact us at info@devlion.co';
$string['notcorrectcode'] = 'Does not look like correct code. Use carefully.';
$string['only_select'] = 'Error - only "SELECT" queries allowed!';
$string['permission_require'] = "You don't have permission, Only site admins can use this plugin.";
$string['pickfromhistory'] = "Pick from report's history";
$string['pleasewait'] = 'Please wait...';
$string['pluginname'] = 'LionAI Reports';
$string['preview'] = 'Preview';
$string['previewnote1'] = 'In the preview table, you can see some of them. To see the full results export the report.';
$string['previewnote2'] = 'Admins can control the amount of records in the preview table through the settings page.';
$string['previewtotal'] = 'A total of {$a} results were received.';
$string['privacy:metadata:local_lionai_reports'] = 'Information about every report, including the user who saved the report and the history of prompts and queries.';
$string['privacy:metadata:local_lionai_reports:name'] = 'The name of the report.';
$string['privacy:metadata:local_lionai_reports:options'] = 'The history of the queries and prompts that the user had used in this report.';
$string['privacy:metadata:local_lionai_reports:timecreated'] = 'The time the report was created.';
$string['privacy:metadata:local_lionai_reports:timemodified'] = 'The time the report was last modified.';
$string['privacy:metadata:local_lionai_reports:userid'] = 'The ID of the user that saved this report.';
$string['prompt'] = 'Prompt';
$string['promptminwords'] = 'The prompt must contain at least 2 words.';
$string['queryerror'] = 'The query could not be executed.';
$string['reportnotfound'] = 'The report was not found.';
$string['sendprompt'] = 'Send prompt';
$string['sqlhistory'] = 'SQL history';
$string['testconnection'] = 'Test connection';
$string['testconnection_badresponse'] = 'Unexpected response from the service: {$a}. Please check the API URL or contact info@devlion.co to resolve the problem.';
$string['testconnection_desc'] = 'Checks the API key and URL entered above (saved or not). Note: this sends one test request to the service and may count towards your prompt quota.';
$string['testconnection_httperror'] = 'The service returned an error (HTTP {$a->code}): {$a->detail}. Please contact info@devlion.co to resolve the problem.';
$string['testconnection_invalidurl'] = 'The API URL "{$a}" is not a valid URL (it must start with http:// or https://).';
$string['testconnection_nokey'] = 'Please enter an API key.';
$string['testconnection_nourl'] = 'Please enter an API URL.';
$string['testconnection_ok'] = 'Connection successful.';
$string['thumbdownbtn'] = 'Dislike';
$string['thumbupbtn'] = 'Like';
$string['timecreated'] = 'Added';
$string['timemodified'] = 'Updated';
$string['trytofix'] = 'Try to fix the query';
$string['trytofixprompt'] = 'try to fix this sql:';
$string['trytofixtooltip'] = 'Attempts to automatically fix errors in the query.
                              If no results are returned, try adjusting the prompt.
                              We recommend using the example template to improve accuracy.';
