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
 * @package    local_smartreport
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/adminlib.php';

// TODO:
// require_once($CFG->dirroot . '/local/smartreport/locallib.php');

// admin_externalpage_setup('local_smartreport', '', $pageparams);

// We are going to need lots of memory and time.
// raise_memory_limit(MEMORY_HUGE);
// set_time_limit(600);

// $output = $PAGE->get_renderer('local_smartreport');

// echo $OUTPUT->header();

$id = optional_param('id', 0, PARAM_INT);

require_login();
$context = context_system::instance();

// require_once($CFG->dirroot.'/local/smartreport/index.class.php');
// require_once($CFG->dirroot.'/local/smartreport/indexs/'.$report->type.'/report.class.php');

// $reportclassname = 'report_'.$report->type;
// $reportclass = new $reportclassname($report);

// TODO:
// if (!$reportclass->check_permissions($USER->id, $context)) {
//     print_error('badpermissions', 'local_smartreport');
// }

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$PAGE->set_title('Smart Reports');
$PAGE->set_heading('Smart Reports');
$PAGE->requires->css(new moodle_url('https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css'));

$PAGE->set_url('/local/smartreport/index.php', ['id' => $id]);

// $hasmanageallcap = has_capability('local/smartreport:managereports', $context);
// $hasmanageowncap = has_capability('local/smartreport:manageownreports', $context);

// $PAGE->set_heading($reportname);
$PAGE->set_cacheable(true);
echo $OUTPUT->header();

$context = new stdClass;
// $context->id = ; // TODO:
// if ($id > 0) {
//     $page = $OUTPUT->render_from_template('local_smartreport/report', $context);
// } else {
    $page = $OUTPUT->render_from_template('local_smartreport/main', $context);
// }

echo $page;

// $PAGE->requires->jquery();
$params = [];
$params['id'] = $id; // TODO:
$PAGE->requires->js_call_amd('local_smartreport/main', 'init', $params);

// core_php_time_limit::raise();
// raise_memory_limit(MEMORY_EXTRA);

// Never reached if download = true.
echo $OUTPUT->footer();
