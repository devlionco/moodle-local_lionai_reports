<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * LionAI Reports library code.
 *
 * @package    local_lionai_reports
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Fetches a list of LionAI reports.
 *
 * @param int $userid The user ID to filter the reports (optional).
 *
 * @return array An array of LionAI reports.
 */
function local_lionai_reports_getlist($userid = 0) {
    global $DB;

    $list = [];
    $table = 'local_lionai_reports';
    $conditions = [];

    if ($userid > 0) {
        $conditions['userid'] = $userid;
    }

    $sort = 'timemodified DESC';
    $records = $DB->get_records($table, $conditions, $sort);

    foreach ($records as $record) {
        $record->link = (new moodle_url('/local/lionai_reports/', ['id' => $record->id]))->out();
        $record->actions = new stdClass();
        $record->actions->sesskey = sesskey();
        $record->actions->deleteactionurl = (new moodle_url('/local/lionai_reports/', ['id' => $record->id]))->out();
        $record->actions->exportcrautoactionurl = (new moodle_url('/local/lionai_reports/',
                ['id' => $record->id, 'export' => 'confreports']))->out();
        $record->actions->exportcrxmlactionurl = (new moodle_url('/local/lionai_reports/',
                ['id' => $record->id, 'export' => 'confreports']))->out();
        $record->actions->exportsqlactionurl = (new moodle_url('/local/lionai_reports/',
                ['id' => $record->id, 'export' => 'sqlformat']))->out();
        $record->actions->exportcsvactionurl = (new moodle_url('/local/lionai_reports/export.php',
                ['id' => $record->id]))->out();

        $options = json_decode($record->options, true);

        $record->lastmessages = local_lionai_reports_getlast_messages($options['history']);

        $list[] = $record;
    }

    return $list;
}

/**
 * Fetches a single LionAI report by ID.
 *
 * @param int $id The ID of the report to fetch.
 *
 * @return object The LionAI report.
 */
function local_lionai_reports_getreport($id = 0) {
    global $DB;

    $list = [];
    $table = 'local_lionai_reports';
    $conditions = [];
    $conditions['id'] = $id;
    $record = $DB->get_record($table, $conditions);

    $history = json_decode($record->options)->history ?? [];

    foreach ($history as $key => $value) {
        if ($value->role == 'assistant') {
            $history[$key]->classes = $value->role . ' ml-4';
        } else {
            $history[$key]->classes = $value->role;
        }
    }

    $record->history = $history;

    return $record;
}

/**
 * Updates a LionAI report.
 *
 * @param int    $id     The ID of the report to update.
 * @param string $action The action to perform (update, delete, etc.).
 * @param mixed  $data   The data to update the report with.
 *
 * @return bool True if the update was successful; otherwise, false.
 */
function local_lionai_reports_updatereport($id = 0, $action = 'update', $data = null) {
    global $DB;
    $updateresult = false;

    switch ($action) {
        case 'update':
            $table = 'local_lionai_reports';
            $conditions = [];
            $conditions['id'] = $id;
            $record = $DB->get_record($table, $conditions);
            $data = json_decode($data);
            foreach ($data as $key => $value) {
                $record->{$key} = $value;
            }
            $updateresult = $DB->update_record($table, $record);
            break;
        default:
            break;
    }

    return $updateresult;
}

/**
 * Adds a new LionAI report.
 *
 * @return int The ID of the newly added report.
 */
function local_lionai_reports_addreport() {
    global $DB, $USER;

    $table = 'local_lionai_reports';

    $currenttimestamp = time(); // Store the current timestamp.

    $newreport = new stdClass();
    $newreport->name = "Report " . date("H:i d.m.y", $currenttimestamp); // Default name based on the current time.
    $newreport->userid = $USER->id; // Current user's ID.
    $newreport->options = null; // Default options is (null).
    $newreport->timecreated = $currenttimestamp; // Use the same timestamp for timecreated.
    $newreport->timemodified = $currenttimestamp; // Use the same timestamp for timemodified.

    $resultid = $DB->insert_record($table, $newreport);

    return $resultid;
}

/**
 * Deletes a LionAI report.
 *
 * @param int $id The ID of the report to delete.
 *
 * @return bool True if the report was deleted successfully; otherwise, false.
 */
function local_lionai_reports_deletereport($id) {
    global $DB;

    $table = 'local_lionai_reports';

    if (!$report = $DB->get_record($table, ['id' => $id])) {
        return false; // Report not found.
    }

    $DB->delete_records($table, ['id' => $id]);

    return true; // Report deleted successfully.
}

/**
 * Executes a SQL query and retrieves the results.
 *
 * @param string $query The SQL query to execute.
 *
 * @return array An array containing the execution status, an optional error message, and the query results.
 */
function local_lionai_reports_getresult($query = '') {
    global $DB;

    $records = [];
    $status = 0;
    $message = '';

    // Remove LIMIT clause.
    $preparedquery = local_lionai_reports_removelimitclause($query);
    $trimmed = $preparedquery != $query;

    // Get the limit of records from configuration, and set a max of 500.
    $limitrecords = get_config('local_lionai_reports', 'lionai_reports_limitrecords');
    $limitrecords = $limitrecords > 500 ? 500 : $limitrecords;

    try {
        $records = $DB->get_records_sql($preparedquery, [], 0, $limitrecords);
        $status = 2;
        $message .= $trimmed ? get_string('limited_to', 'local_lionai_reports', $limitrecords) : '';
    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    return [$status, $message, $records];
}

/**
 * Remove the LIMIT clause from an SQL query.
 *
 * @param string $query The SQL query to process.
 *
 * @return string The SQL query without the LIMIT clause.
 */
function local_lionai_reports_removelimitclause($query) {
    $pattern = '/\s+LIMIT\s+\d+(?:\s*,\s*\d+)?\s*$/i';
    return preg_replace($pattern, '', $query);
}

/**
 * Appends a history item to a report.
 *
 * @param int    $reportid     The ID of the report to which the history item is added.
 * @param object $historyitem  The history item to add.
 *
 * @return mixed The result of updating the history item.
 */
function local_lionai_reports_put_history($reportid, $historyitem) {
    global $DB;

    $report = local_lionai_reports_getreport($reportid);
    $lasthistoryitem = end($report->history);

    if ($lasthistoryitem && $lasthistoryitem->role === $historyitem->role && $lasthistoryitem->content === $historyitem->content) {
        return false;
    }
    $report->history[] = $historyitem;
    $options = $report->options ? json_decode($report->options) : new stdClass;
    $options->history = $report->history;
    $report->options = json_encode($options);
    $report->timemodified = time();
    $response = $DB->update_record('local_lionai_reports', $report);

    return $response;
}

/**
 * Get the last messages for user and assistant roles from history data.
 *
 * @param array $historydata The history data array.
 * @return array An associative array containing 'lastusercontent' and 'lastassistantcontent'.
 */
function local_lionai_reports_getlast_messages($historydata) {
    // Initialize variables to store the last items for each role.
    $lastusercontent = null;
    $lastassistantcontent = null;

    if ($historydata) {
        // Loop through the history items in reverse order.
        foreach (array_reverse($historydata) as $item) {
            if ($item['role'] === 'user' && $lastusercontent === null) {
                $lastusercontent = $item['content'];
            }
            if ($item['role'] === 'assistant' && $lastassistantcontent === null) {
                $lastassistantcontent = $item['content'];
            }
            // If both last items are found, break out of the loop.
            if ($lastusercontent !== null && $lastassistantcontent !== null) {
                break;
            }
        }
    }
    // Return the last messages in an associative array.
    return [
        'lastusercontent' => $lastusercontent,
        'lastassistantcontent' => $lastassistantcontent,
    ];
}

/**
 * Exports a configurable report as an XML file or auto-imports it into a course.
 *
 * @param int  $id         The ID of the report to export or auto-import.
 * @param bool $autoimport True if auto-import is enabled; otherwise, false.
 *
 * @return void
 */
function local_lionai_reports_export_confreports($id, $autoimport = false) {
    global $CFG, $PAGE, $DB;

    $report = local_lionai_reports_getreport($id);

    $context = context_system::instance();
    $PAGE->set_context($context);

    $report->lastassistantcontent = json_decode($report->options, true) ? local_lionai_reports_getlast_messages(
        json_decode($report->options, true)['history']
        )['lastassistantcontent'] : '';
    $downloadfilename = clean_filename(format_string($report->name)) . '.xml';

    $data = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    $data .= "<report version=\"2020110300\">";

    $reportdata = [
        'visible' => '1',
        'summaryformat' => '1',
        'type' => 'sql',
        'pagination' => '0',
        'export' => '',
        'jsordering' => '1',
        'global' => '0',
        'lastexecutiontime' => '1',
        'cron' => '0',
        'remote' => '0',
    ];

    $reportdata['name'] = "[lionai_reports] promt: " . $report->name;
    $reportdata['summary'] = $report->name;

    $components = [
        'customsql' => [
            'config' => (object) [
                'querysql' => $report->lastassistantcontent,
                'courseid' => 1,
                'submitbutton' => 'Save changes',
                'reportcategories' => '0',
                'reportsincategory' => '0',
                'remotequerysql' => '',
            ],
        ],
    ];

    $components = local_lionai_reports_cr_serialize($components);

    $reportdata['components'] = base64_encode($components);

    foreach ($reportdata as $key => $value) {
        $data .= "<$key><![CDATA[$value]]></$key>\n";
    }

    $data .= "</report>";

    if (!$autoimport) {
        if (strpos($CFG->wwwroot, 'https://') === 0) {
            @header('Cache-Control: max-age=10');
            @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
            @header('Pragma: ');
        } else {
            @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
            @header('Pragma: no-cache');
        }
        header("Content-type: text/xml; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$downloadfilename\"");

        print($data);
    }

    $courseid = 1; // TODO: Course.
    $course = $DB->get_record("course", ['id' => $courseid]);

    // If autoimport && function cr_import_xml is present.
    if ($autoimport) {
        require_once($CFG->dirroot . '/blocks/configurable_reports/locallib.php');
        if (function_exists('cr_import_xml')) {
            $reportid = local_lionai_reports_cr_import_xml($data, $course);
            if ($reportid) {
                redirect("$CFG->wwwroot/blocks/configurable_reports/viewreport.php?id={$reportid}",
                    get_string('reportcreated', 'block_configurable_reports'));
            } else {
                print('errorimporting');
            }
        }
    }
}

/**
 * Unserializes a variable that was previously serialized with local_lionai_reports_cr_serialize.
 *
 * @param string $var The serialized variable.
 *
 * @return mixed The unserialized variable.
 */
function local_lionai_reports_cr_unserialize($var) {
    // It's needed to convert the object to stdClass to avoid __PHP_Incomplete_Class error.
    $var = preg_replace('/O:6:"object"/', 'O:8:"stdClass"', $var);
    // To make SQL queries compatible with PostgreSQL it's needed to replace " to '.
    $var = preg_replace('/THEN\+%22(.+?)%22/', 'THEN+%27${1}%27', $var);
    $var = preg_replace('/%60/', '+++', $var);

    return local_lionai_reports_urldecode_recursive(unserialize($var));
}

/**
 * URL-decodes a variable.
 *
 * @param mixed $var The variable to decode.
 *
 * @return mixed The decoded variable.
 */
function local_lionai_reports_urldecode_recursive($var) {
    if (is_object($var)) {
        $newvar = new \stdClass();
        $properties = get_object_vars($var);
        foreach ($properties as $property => $value) {
            $newvar->$property = local_lionai_reports_urldecode_recursive($value);
        }
    } else if (is_array($var)) {
        $newvar = [];
        foreach ($var as $property => $value) {
            $newvar[$property] = local_lionai_reports_urldecode_recursive($value);
        }
    } else if (is_string($var)) {
        $newvar = urldecode($var);
    } else {
        $newvar = $var;
    }

    return $newvar;
}

/**
 * Serializes a variable with support for URL encoding.
 *
 * @param mixed $var The variable to serialize.
 *
 * @return string The serialized and URL-encoded variable.
 */
function local_lionai_reports_cr_serialize($var) {
    return serialize(local_lionai_reports_urlencode_recursive($var));
}

/**
 * URL-encodes a variable.
 *
 * @param mixed $var The variable to encode.
 *
 * @return mixed The encoded variable.
 */
function local_lionai_reports_urlencode_recursive($var) {
    if (is_object($var)) {
        $newvar = new \stdClass();
        $properties = get_object_vars($var);
        foreach ($properties as $property => $value) {
            $newvar->$property = local_lionai_reports_urlencode_recursive($value);
        }
    } else if (is_array($var)) {
        $newvar = [];
        foreach ($var as $property => $value) {
            $newvar[$property] = local_lionai_reports_urlencode_recursive($value);
        }
    } else if (is_string($var)) {
        $newvar = urlencode($var);
    } else {
        // Nulls, integers, etc.
        $newvar = $var;
    }

    return $newvar;
}

/**
 * Exports the report sql content as an sql file.
 *
 * @param int  $id         The ID of the report to export or auto-import.
 *
 * @return void
 */
function local_lionai_reports_export_sqlformat($id) {
    global $CFG, $PAGE, $DB;

    $report = local_lionai_reports_getreport($id);

    $context = context_system::instance();
    $PAGE->set_context($context);

    $report->lastassistantcontent = json_decode($report->options, true) ? local_lionai_reports_getlast_messages(
            json_decode($report->options, true)['history']
    )['lastassistantcontent'] : '';
    $downloadfilename = clean_filename(format_string($report->name)) . '.sql';

    $data = $report->lastassistantcontent;

    if (strpos($CFG->wwwroot, 'https://') === 0) {
        @header('Cache-Control: max-age=10');
        @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        @header('Pragma: ');
    } else {
        @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        @header('Pragma: no-cache');
    }
    header("Content-type: text/xml; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$downloadfilename\"");

    print($data);
}

/**
 * Exports the report to configurable reports plugin.
 *
 * @param string $xml xml formate of the report.
 * @param int $course course id.
 * @return void
 */
function local_lionai_reports_cr_import_xml($xml, $course) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/lib/xmlize.php');
    $data = xmlize($xml, 1, 'UTF-8');

    if (isset($data['report']['@']['version'])) {
        $newreport = new stdclass;
        foreach ($data['report']['#'] as $key => $val) {
            if ($key == 'components') {
                $val[0]['#'] = base64_decode(trim($val[0]['#']));
                // Fix url_encode " and ' when importing SQL queries.
                $tempcomponents = cr_unserialize($val[0]['#']);
                if (array_key_exists('customsql', $tempcomponents)) {
                    $tempcomponents['customsql']['config']->querysql =
                            str_replace("\'", "'", $tempcomponents['customsql']['config']->querysql);
                    $tempcomponents['customsql']['config']->querysql =
                            str_replace('\"', '"', $tempcomponents['customsql']['config']->querysql);
                }
                $val[0]['#'] = cr_serialize($tempcomponents);
            }
            $newreport->{$key} = trim($val[0]['#']);
        }
        $newreport->courseid = $course->id;
        $newreport->ownerid = $USER->id;
        $newreport->name .= " (" . userdate(time()) . ")";

        $id = $DB->insert_record('block_configurable_reports', $newreport);

        if (!$id) {
            return false;
        }
        return $id;
    }
    return false;
}
