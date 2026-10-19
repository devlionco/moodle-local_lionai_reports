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

namespace local_lionai_reports\admin;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Admin setting that renders a "Test connection" button (no value is stored).
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_testconnection extends \admin_setting {
    /**
     * Constructor.
     *
     * @param string $name Unique setting name.
     */
    public function __construct($name) {
        parent::__construct(
            $name,
            get_string('testconnection', 'local_lionai_reports'),
            get_string('testconnection_desc', 'local_lionai_reports'),
            ''
        );
    }

    /**
     * Nothing is stored.
     *
     * @return bool
     */
    public function get_setting() {
        return true;
    }

    /**
     * Nothing is stored.
     *
     * @param mixed $data
     * @return string
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Renders the button and result area.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $PAGE, $OUTPUT;

        $PAGE->requires->js_call_amd('local_lionai_reports/testconnection', 'init', [
            'local_lionai_reports/lionai_reports_apikey',
            'local_lionai_reports/lionai_reports_apiurl',
        ]);

        $button = \html_writer::tag('button', get_string('testconnection', 'local_lionai_reports'), [
            'type' => 'button',
            'class' => 'btn btn-secondary',
            'id' => 'local_lionai_reports_testconnection',
        ]);
        $result = \html_writer::div('', 'mt-2', [
            'id' => 'local_lionai_reports_testconnection_result',
            'role' => 'status',
            'aria-live' => 'polite',
        ]);

        return format_admin_setting($this, $this->visiblename, $button . $result, $this->description, true, '', null, $query);
    }
}
