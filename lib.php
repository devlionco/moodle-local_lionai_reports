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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/lionai_reports/locallib.php');

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 *
 * @return \core\output\inplace_editable
 */
function local_lionai_reports_inplace_editable($itemtype, $itemid, $newvalue) {
    \external_api::validate_context(context_system::instance());
    if ($itemtype=='lioanai_reports_reportname_editable') {
        local_lionai_reports_updatereport($itemid, 'update', json_encode(['name' => $newvalue]));
        $url = new moodle_url('/local/lionai_reports', ['id' => $itemid]);
        $displayvalue = html_writer::link($url, $newvalue);
    }
    return new \core\output\inplace_editable(
            'local_lioanai_reports',
            'lioanai_reports_reportname_editable',
            $itemid,
            true,
            $displayvalue,
            $newvalue,
    );
}
