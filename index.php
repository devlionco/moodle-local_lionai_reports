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
 * Run the code checker from the web.
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

$id = optional_param('id', 0, PARAM_INT);
$export = optional_param('export', '', PARAM_ALPHANUM);

$autoimport = isset($_POST['autoimport']) && $_POST['autoimport'] == 1 ? 1 : 0;

// TODO: add has_capapblity.
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
            // TODO: local_lionai_reports_export reportbuilder.
            exit;
            break;
        default:
            break;
    }
}

// Check if the 'action' parameter is set to 'create' in the POST request.
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $id = local_lionai_reports_addreport();
    }
    if ($_POST['action'] === 'delete') {
        local_lionai_reports_deletereport($id);
        $id = 0;
    }
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$PAGE->set_title('LionAI Reports');

$allreportsurl = $CFG->wwwroot . '/local/lionai_reports';
$headinghtml = html_writer::link($allreportsurl, get_string('allreports', 'local_lionai_reports'));
$PAGE->set_heading($headinghtml, false);

$PAGE->requires->css(new moodle_url('https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css'));

$PAGE->set_url('/local/lionai_reports/index.php', ['id' => $id]);

// TODO: has_capability .

$PAGE->set_cacheable(true);
echo $OUTPUT->header();

$context = new stdClass;
$page = $OUTPUT->render_from_template('local_lionai_reports/main', $context);

echo $page;

$params = [];
$params['id'] = $id;
$PAGE->requires->js_call_amd('local_lionai_reports/main', 'init', $params);

echo $OUTPUT->footer();
