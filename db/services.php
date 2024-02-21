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
 * External tool external functions and service definitions.
 *
 * @package    local_lionai_reports
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.1
 */

defined('MOODLE_INTERNAL') || die;

$functions = [

    'local_lionai_reports_getlist' => [
        'classname' => 'local_lionai_reports_external',
        'methodname' => 'getlist',
        'description' => 'Get a list of reports',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_lionai_reports_getreport' => [
        'classname' => 'local_lionai_reports_external',
        'methodname' => 'getreport',
        'description' => 'Get report',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_lionai_reports_updatereport' => [
        'classname' => 'local_lionai_reports_external',
        'methodname' => 'updatereport',
        'description' => 'update report',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_lionai_reports_send_prompt' => [
        'classname' => 'local_lionai_reports_external',
        'methodname' => 'send_prompt',
        'description' => 'send_prompt.',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_lionai_reports_rate_prompt' => [
        'classname' => 'local_lionai_reports_external',
        'methodname' => 'rate_prompt',
        'description' => 'rate_prompt.',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_lionai_reports_getresult' => [
        'classname' => 'local_lionai_reports_external',
        'methodname' => 'getresult',
        'description' => 'Get result',
        'type' => 'read',
        'ajax' => true,
    ],
];
