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
 * Smart Report library code.
 *
 * @package    local_smartreport
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function local_smartreport_getlist($userid = 0) {
    global $DB;

    // Initialize the list to store your items
    $list = [];

    $table = 'local_smartreport';

    // Define conditions for fetching data
    $conditions = array();

    // Check if a specific userid is provided
    if ($userid > 0) {
        $conditions['userid'] = $userid;
    }

    // Fetch data from the database with conditions
    $records = $DB->get_records($table, $conditions);

    // Loop through the records and populate your list
    foreach ($records as $record) {

        // $record->name =
        // Generate the link
        $record->link = (new moodle_url('/local/smartreport/', array('id' => $record->id)))->out();

        // Add the item to the list
        $list[] = $record;
    }

    return $list;
}

function local_smartreport_getreport($id = 0) {
    global $DB;

    // Initialize the list to store your items
    $list = [];

    $table = 'local_smartreport';

    // Define conditions for fetching data
    $conditions = array();
    $conditions['id'] = $id;

    // Fetch data from the database with conditions
    $record = $DB->get_record($table, $conditions);

    // TODO: Prepare report according to report settings.

    $history = json_decode($record->options)->history;

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

function local_smartreport_getresult($query = '') {
    global $DB;

    // TODO: STRONG SQL VALIDATION!!!

    // $records = $DB->get_records_sql($query, [], 0, 10);

    $records = [];
    $status = false;
    $message = '';
    try {
        $records = $DB->get_records_sql($query, [], 0, 10);
        $status = true;
    } catch (Exception $e) {
        $message = $e->getMessage();

        // Handle the error or log it as needed
        // For example, you can log the error message to a file or display it to the user
        // You can also rethrow the exception if needed.

        // For now, we'll just print the error message as an example
        // echo "An error occurred: " . $errorMessage;
    }

    return [$status, $message, $records];
}
