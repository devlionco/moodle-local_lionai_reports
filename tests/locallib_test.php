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
 * Unit tests for (some of) local/lionai_reports/locallib.php.
 *
 * @package    local_lionai_reports
 * @category   test
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lionai_reports;

use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/lionai_reports/locallib.php');

/**
 * Unit tests for (some of) local/lionai_reports/locallib.php.
 *
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class locallib_test extends \advanced_testcase {

    /**
     * PHPUnit test case for the local_lionai_reports_removelimitclause function.
     *
     * @covers ::local_lionai_reports_removelimitclause
     */
    public function test_local_lionai_reports_removelimitclause() {
        // Test case: Query with a LIMIT clause.
        $querywithlimit = "SELECT * FROM table LIMIT 10";
        $expectedquery = "SELECT * FROM table";
        $this->assertEquals($expectedquery, local_lionai_reports_removelimitclause($querywithlimit));

        // Test case: Query without a LIMIT clause.
        $querywithoutlimit = "SELECT * FROM table";
        $this->assertEquals($querywithoutlimit, local_lionai_reports_removelimitclause($querywithoutlimit));
    }

    /**
     * PHPUnit test case for the local_lionai_reports_addreport function.
     *
     * @covers ::local_lionai_reports_addreport
     */
    public function test_local_lionai_reports_addreport() {
        global $DB, $USER;

        $this->resetAfterTest();

        // Create a test user.
        $testuser = $this->getDataGenerator()->create_user();
        $USER->id = $testuser->id;

        // Call the function to add a report.
        $resultid = local_lionai_reports_addreport();

        $table = 'local_lionai_reports';
        $conditions = [];
        $conditions['id'] = $resultid;
        $record = $DB->get_record($table, $conditions);

        $this->assertNull($record->options);
        $this->assertEquals($USER->id, $record->userid);
    }

    /**
     * PHPUnit test case for the local_lionai_reports_getreport function.
     *
     * @covers ::local_lionai_reports_getreport
     */
    public function test_local_lionai_reports_getreport() {
        global $DB, $USER;

        $this->resetAfterTest();

        // Create a test user.
        $testuser = $this->getDataGenerator()->create_user();
        $USER->id = $testuser->id;

        $table = 'local_lionai_reports';

        $currenttimestamp = time(); // Store the current timestamp.

        $newreport = new stdClass();
        $newreport->name = "Report " . date("H:i d.m.y", $currenttimestamp); // Default name based on the current time.
        $newreport->userid = $USER->id; // Current user's ID.

        $newreport->options = null;

        $newreport->timecreated = $currenttimestamp; // Use the same timestamp for timecreated.
        $newreport->timemodified = $currenttimestamp; // Use the same timestamp for timemodified.

        $resultid = $DB->insert_record($table, $newreport);

        $report = local_lionai_reports_getreport($resultid);

        $this->assertEquals("Report " . date("H:i d.m.y", $currenttimestamp), $report->name);
        $this->assertEquals($USER->id, $report->userid);
        $this->assertNull($report->options);
        $this->assertEquals($currenttimestamp, $report->timecreated);
        $this->assertEquals($currenttimestamp, $report->timemodified);
    }

    /**
     * PHPUnit test case for the local_lionai_reports_updatereport function.
     *
     * @covers ::local_lionai_reports_updatereport
     */
    public function test_local_lionai_reports_updatereport() {
        global $DB, $USER;

        $this->resetAfterTest();

        // Create a test user.
        $testuser = $this->getDataGenerator()->create_user();
        $USER->id = $testuser->id;

        $table = 'local_lionai_reports';

        $currenttimestamp = time(); // Store the current timestamp.

        $newreport = new stdClass();
        $newreport->name = "Report " . date("H:i d.m.y", $currenttimestamp); // Default name based on the current time.
        $newreport->userid = $USER->id; // Current user's ID.

        $newreport->options = null;

        $newreport->timecreated = $currenttimestamp; // Use the same timestamp for timecreated.
        $newreport->timemodified = $currenttimestamp; // Use the same timestamp for timemodified.

        $resultid = $DB->insert_record($table, $newreport);

        $data = new stdClass;
        $data->name = 'New report name';
        $data->timemodified = $currenttimestamp + 3600;

        local_lionai_reports_updatereport($resultid, 'update', json_encode($data));

        $table = 'local_lionai_reports';
        $conditions = [];
        $conditions['id'] = $resultid;
        $record = $DB->get_record($table, $conditions);

        $this->assertEquals($data->name, $record->name);
        $this->assertNull($record->options);
        $this->assertEquals($data->timemodified, $record->timemodified);
    }

    /**
     * PHPUnit test case for the local_lionai_reports_deletereport function.
     *
     * @covers ::local_lionai_reports_deletereport
     */
    public function test_local_lionai_reports_deletereport() {
        global $DB, $USER;

        $this->resetAfterTest();

        // Create a test user.
        $testuser = $this->getDataGenerator()->create_user();
        $USER->id = $testuser->id;

        $table = 'local_lionai_reports';

        $currenttimestamp = time(); // Store the current timestamp.

        $newreport = new stdClass();
        $newreport->name = "Report " . date("H:i d.m.y", $currenttimestamp); // Default name based on the current time.
        $newreport->userid = $USER->id; // Current user's ID.

        $newreport->options = null;

        $newreport->timecreated = $currenttimestamp; // Use the same timestamp for timecreated.
        $newreport->timemodified = $currenttimestamp; // Use the same timestamp for timemodified.

        $resultid = $DB->insert_record($table, $newreport);

        // Check if the report was inserted successfully.
        $this->assertTrue(!empty($resultid));

        // Call the function to delete the report.
        $deleted = local_lionai_reports_deletereport($resultid);

        // Check if the report was deleted successfully.
        $this->assertTrue($deleted);

        // Check if the report no longer exists in the database.
        $this->assertFalse($DB->record_exists($table, ['id' => $resultid]));
    }

    /**
     * PHPUnit test case for the local_lionai_reports_getresult function.
     *
     * @covers ::local_lionai_reports_getresult
     */
    public function test_local_lionai_reports_getresult() {
        global $DB, $USER;

        $this->resetAfterTest();

        // Create a test user.
        $testuser = $this->getDataGenerator()->create_user();

        // Set the current user to the test user.
        $USER->id = $testuser->id;

        // Test query.
        $query = 'SELECT * FROM {user} WHERE id = ' . $testuser->id;

        // Execute the function.
        list($status, $message, $records) = local_lionai_reports_getresult($query);

        // Check if the status is set to 2, indicating a successful query execution.
        $this->assertEquals(2, $status);

        // Check that the error message is empty (no exceptions were thrown).
        $this->assertEmpty($message);

        // Check that the result is not empty.
        $this->assertNotEmpty($records);

        // Check if the retrieved user's ID matches the created test user's ID.
        $this->assertEquals($testuser->id, $records[$testuser->id]->id);
    }

    /**
     * PHPUnit test case for the local_lionai_reports_put_history function.
     *
     * @covers ::local_lionai_reports_put_history
     */
    public function test_local_lionai_reports_put_history() {
        global $DB, $USER;

        $this->resetAfterTest();

        // Create a test report and a test history item.
        $testreport = $this->create_test_report();
        $testhistoryitem = (object) [
            'role' => 'user',
            'content' => 'Test Content',
        ];

        // Call the function to append the history item to the report.
        $response = local_lionai_reports_put_history(
            $testreport->id,
            $testhistoryitem);

        // Check if the function returned a non-false response.
        $this->assertNotFalse($response);

        // Retrieve the updated report from the database.
        $updatedreport = local_lionai_reports_getreport($testreport->id);

        // Check if the history item was added to the report's history.
        $this->assertCount(count($testreport->history) + 1, $updatedreport->history);

        // Check if the last history item in the report matches the test history item.
        $lasthistoryitem = end($updatedreport->history);
        $this->assertEquals($testhistoryitem->role, $lasthistoryitem->role);
        $this->assertEquals($testhistoryitem->content, $lasthistoryitem->content);
    }

    /**
     * Helper function to create a test report.
     */
    private function create_test_report() {
        global $DB, $USER;

        $table = 'local_lionai_reports';

        $currenttimestamp = time(); // Store the current timestamp.

        $newreport = new stdClass();
        $newreport->name = "Report " . date("H:i d.m.y", $currenttimestamp); // Default name based on the current time.
        $newreport->userid = $USER->id; // Current user's ID.

        $newreport->options = null;

        $newreport->timecreated = $currenttimestamp; // Use the same timestamp for timecreated.
        $newreport->timemodified = $currenttimestamp; // Use the same timestamp for timemodified.

        $resultid = $DB->insert_record($table, $newreport);

        // Create the test report object with the generated ID.
        $testreport = (object) [
            'id' => $resultid,
            'history' => [],
        ];
        return $testreport;
    }

}
