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
 * local lionai_reports main page.
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/lionai_reports/locallib.php');

require_login();
if (!is_siteadmin()) {
    throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
    return;
}

global $DB;

$id = optional_param('id', 0, PARAM_INT);
$export = optional_param('export', '', PARAM_ALPHANUM);
$action = optional_param('action', null, PARAM_TEXT);
$autoimport = optional_param('autoimport', null, PARAM_INT);
$autoimport = isset($autoimport) && $autoimport == 1 ? 1 : 0;

if ($export) {
    switch ($export) {
        case 'confreports':
            local_lionai_reports_export_confreports($id, $autoimport);
            exit;
            break;
        case 'sqlformat':
            local_lionai_reports_export_sqlformat($id);
            exit;
            break;
        case 'reportbuilder':
            exit;
            break;
        default:
            break;
    }
}

// Check if the 'action' parameter is set to 'create' in the POST request.
if (isset($action)) {
    if ($action === 'create') {
        $id = local_lionai_reports_addreport();
    }
    if ($action === 'delete') {
        local_lionai_reports_deletereport($id);
        $id = 0;
    }
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$PAGE->set_title('LionAI Reports');

$allreportsurl = $CFG->wwwroot . '/local/lionai_reports/index.php';
$headinghtml = html_writer::link($allreportsurl, get_string('allreports', 'local_lionai_reports'));
$PAGE->set_heading($headinghtml, false);
$PAGE->navbar->add(get_string('administrationsite'), $CFG->wwwroot . '/admin/category.php?category=lionai_reports');
$PAGE->navbar->add(get_string('allreports', 'local_lionai_reports'), $allreportsurl);
if ($id !== 0) {
    $PAGE->navbar->add($DB->get_field('local_lionai_reports', 'name', ['id' => $id]), $allreportsurl);
}

$PAGE->requires->css('/local/lionai_reports/css/datatables.css');

$PAGE->set_url('/local/lionai_reports/index.php', ['id' => $id]);

$PAGE->set_cacheable(true);
echo $OUTPUT->header();

$context = new stdClass;
$page = $OUTPUT->render_from_template('local_lionai_reports/main', $context);

echo $page;

$params = [];
$params['id'] = $id;
$PAGE->requires->js_call_amd('local_lionai_reports/main', 'init', $params);

echo $OUTPUT->footer();
