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
 * External tool module external API
 *
 * @package    local_lionai_reports
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.1
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');
require_once("$CFG->libdir/csvlib.class.php");

global $PAGE, $CFG, $DB, $OUTPUT, $USER;

$reportid = required_param('id', PARAM_INT);
$filetype = optional_param('filetype', 'csv', PARAM_ALPHA);

require_login();
if (!is_siteadmin()) {
    throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
    return;
}

// Prepare the data.
$report = local_lionai_reports_getreport($reportid);
$report->lastassistantcontent = json_decode($report->options, true) ? local_lionai_reports_getlast_messages(
        json_decode($report->options, true)['history']
)['lastassistantcontent'] : '';
$sql = $report->lastassistantcontent;
list($status, $message, $data) = local_lionai_reports_getresult($sql, true);

if (empty($data)) {
    throw new \moodle_exception('No data available for export', 'local_lionai_reports');
}

$keys = array_keys((array)$data[array_key_first($data)]);

$context = context_system::instance();
$PAGE->set_context($context);

// Set filename based on filetype
if ($filetype === 'csv') {
    $filename = clean_filename(format_string($report->name)) . '.csv';
} else if ($filetype === 'xls') {
    $filename = clean_filename(format_string($report->name)) . '.xls';
} else {
    // Default to CSV
    $filename = clean_filename(format_string($report->name)) . '.csv';
    $filetype = 'csv';
}

$downloadfilename = clean_filename($filename);

// Handle CSV export
if ($filetype === 'csv') {
    $csvexport = new csv_export_writer('comma');
    $csvexport->set_filename($downloadfilename);
    
    $csvexport->add_data($keys);
    
    foreach ($data as $record) {
        $csvexport->add_data((array)$record);
    }
    
    $csvexport->download_file();
} else if ($filetype === 'xls') {
    // Handle Excel export
    require_once("$CFG->libdir/excellib.class.php");
    
    $workbook = new MoodleExcelWorkbook($downloadfilename);
    $worksheet = $workbook->add_worksheet('Report');
    
    // Add headers
    $col = 0;
    foreach ($keys as $key) {
        $worksheet->write_string(0, $col, $key);
        $col++;
    }
    
    // Add data
    $row = 1;
    foreach ($data as $record) {
        $col = 0;
        foreach ((array)$record as $value) {
            $worksheet->write_string($row, $col, $value);
            $col++;
        }
        $row++;
    }
    
    $workbook->close();
}
