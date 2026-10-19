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
 * Exports the SQL of a LionAI report as a CSV file.
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

global $PAGE, $CFG, $DB, $OUTPUT, $USER;

$reportid = required_param('id', PARAM_INT);

require_login();
if (!is_siteadmin()) {
    throw new \moodle_exception('permission_require', 'local_lionai_reports');
    return;
}

// Prepare the data.
$report = local_lionai_reports_getreport($reportid);
$report->lastassistantcontent = $report->lastmessages['lastassistantcontent'] ?? '';
$sql = $report->lastassistantcontent;

$context = context_system::instance();
$PAGE->set_context($context);

$filename = clean_filename(format_string($report->name));

// Streams the rows straight to the browser (capped by the export limit setting).
local_lionai_reports_export_csv($sql, $filename);
