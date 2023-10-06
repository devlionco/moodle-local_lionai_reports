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
 * @package    local_smartreport
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.9
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'local_smartreport_getlist' => array(
        'classname' => 'local_smartreport_external',
        'methodname' => 'getlist',
        'description' => 'Get a list of reports',
        'type' => 'read',
        // 'capabilities'  => 'moodle/site:config',
        'ajax' => true,
    ),
    'local_smartreport_getreport' => array(
        'classname' => 'local_smartreport_external',
        'methodname' => 'getreport',
        'description' => 'Get report',
        'type' => 'read',
        // 'capabilities'  => 'moodle/site:config',
        'ajax' => true,
    ),
    'local_smartreport_updatereport' => array(
        'classname' => 'local_smartreport_external',
        'methodname' => 'updatereport',
        'description' => 'update report',
        'type' => 'write',
        // 'capabilities'  => 'moodle/site:config',
        'ajax' => true,
    ),
    'local_smartreport_send_prompt' => array(
        'classname' => 'local_smartreport_external',
        'methodname' => 'send_prompt',
        'description' => 'send_prompt.',
        'type' => 'write',
        'ajax' => true,
    ),
    'local_smartreport_getresult' => array(
        'classname' => 'local_smartreport_external',
        'methodname' => 'getresult',
        'description' => 'Get result',
        'type' => 'read',
        // 'capabilities'  => 'moodle/site:config',
        'ajax' => true,
    ),
);
