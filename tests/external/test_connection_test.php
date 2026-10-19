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

namespace local_lionai_reports\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Unit tests for the test_connection external function.
 *
 * @package    local_lionai_reports
 * @category   test
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_lionai_reports\external\test_connection
 */
final class test_connection_test extends \externallib_advanced_testcase {
    /**
     * A missing key (given and saved) is reported without contacting the service.
     */
    public function test_missing_key(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $result = test_connection::execute('', 'https://example.invalid/v1');
        $result = \core_external\external_api::clean_returnvalue(test_connection::execute_returns(), $result);
        $this->assertFalse($result['success']);
        $this->assertSame(get_string('testconnection_nokey', 'local_lionai_reports'), $result['message']);
    }

    /**
     * A missing URL (given and saved) is reported without contacting the service.
     */
    public function test_missing_url(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('lionai_reports_apiurl', '', 'local_lionai_reports');

        $result = test_connection::execute('key', '');
        $this->assertFalse($result['success']);
        $this->assertSame(get_string('testconnection_nourl', 'local_lionai_reports'), $result['message']);
    }

    /**
     * Saved settings are used when the parameters are empty.
     */
    public function test_saved_settings_are_used(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('lionai_reports_apikey', 'saved', 'local_lionai_reports');
        set_config('lionai_reports_apiurl', 'not a url', 'local_lionai_reports');

        // The saved URL is invalid, which proves both saved values were picked up (key passed the first check).
        $result = test_connection::execute();
        $this->assertFalse($result['success']);
        $this->assertSame(get_string('testconnection_invalidurl', 'local_lionai_reports', 'not a url'), $result['message']);
    }

    /**
     * Data for invalid URLs.
     *
     * @return array
     */
    public static function invalid_url_provider(): array {
        return [
            'no scheme' => ['example.com/v1'],
            'ftp scheme' => ['ftp://example.com/v1'],
            'javascript scheme' => ['javascript:alert(1)'],
            'no host' => ['https:///path'],
        ];
    }

    /**
     * Malformed URLs are rejected before any request is made.
     *
     * @dataProvider invalid_url_provider
     * @param string $url
     */
    public function test_invalid_url(string $url): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $result = test_connection::execute('key', $url);
        $this->assertFalse($result['success']);
        $this->assertSame(get_string('testconnection_invalidurl', 'local_lionai_reports', s($url)), $result['message']);
    }

    /**
     * Users without the manage capability can not use the function.
     */
    public function test_requires_capability(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->expectException(\required_capability_exception::class);
        test_connection::execute('key', 'https://example.invalid/v1');
    }
}
