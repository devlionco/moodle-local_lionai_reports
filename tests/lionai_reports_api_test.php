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
 * Unit tests for the LionAI API client.
 *
 * @package    local_lionai_reports
 * @category   test
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_lionai_reports;

/**
 * Unit tests for the LionAI API client.
 *
 * @covers \local_lionai_reports\lionai_reports_api
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lionai_reports_api_test extends \advanced_testcase {
    /**
     * Data for extract_sql.
     *
     * @return array
     */
    public static function extract_sql_provider(): array {
        $fence = str_repeat(chr(96), 3);
        return [
            'plain select' => ['SELECT id FROM {user}', 'SELECT id FROM {user}', 2],
            'fenced with language' => ["Here:\n{$fence}sql\nSELECT id FROM {user};\n{$fence}\nDone", 'SELECT id FROM {user}', 2],
            'fenced without language' => ["{$fence}\nSELECT id FROM {course};\n{$fence}", 'SELECT id FROM {course}', 2],
            'fenced but not a query' => ["{$fence}sql\nSHOW TABLES\n{$fence}", 'SHOW TABLES', 0],
            'inline statement' => ['Use SELECT id FROM {user} WHERE id = 1; to get it', 'SELECT id FROM {user} WHERE id = 1', 2],
            'no sql at all' => ['I can not help with that', '', 0],
        ];
    }

    /**
     * The SQL is extracted from the AI answer.
     *
     * @dataProvider extract_sql_provider
     * @param string $raw
     * @param string $expectedsql
     * @param int $expectedcorrect
     */
    public function test_extract_sql(string $raw, string $expectedsql, int $expectedcorrect): void {
        $api = new lionai_reports_api('key', 'https://example.invalid/v1');
        [$sql, $correct] = $api->extract_sql($raw);
        $this->assertSame($expectedsql, $sql);
        $this->assertSame($expectedcorrect, $correct);
    }

    /**
     * Only text with SELECT and FROM looks like a query.
     */
    public function test_is_valid_sql(): void {
        $api = new lionai_reports_api('key', 'https://example.invalid/v1');
        $this->assertTrue($api->is_valid_sql('select 1 from {user}'));
        $this->assertFalse($api->is_valid_sql('select 1'));
        $this->assertFalse($api->is_valid_sql('from {user}'));
    }

    /**
     * Data for http_error_message.
     *
     * @return array
     */
    public static function http_error_provider(): array {
        return [
            'forbidden means bad key' => [403, '{"message":"Forbidden"}', 'invalidapikey'],
            'missing token means bad url' => [403, '{"message":"Missing Authentication Token"}', 'invalidapiurl'],
            'not found means bad url' => [404, '{"message":"Not Found"}', 'invalidapiurl'],
            'other error' => [500, '<b>Boom</b>', 'testconnection_httperror'],
        ];
    }

    /**
     * HTTP failures are translated to a helpful message.
     *
     * @dataProvider http_error_provider
     * @param int $code
     * @param string $body
     * @param string $stringid
     */
    public function test_http_error_message(int $code, string $body, string $stringid): void {
        $api = new lionai_reports_api('key', 'https://example.invalid/v1');
        foreach (['httpcode' => $code, 'rawresponse' => $body] as $name => $value) {
            $property = new \ReflectionProperty($api, $name);
            $property->setAccessible(true);
            $property->setValue($api, $value);
        }
        $method = new \ReflectionMethod($api, 'http_error_message');
        $method->setAccessible(true);
        $message = $method->invoke($api);

        if ($stringid === 'testconnection_httperror') {
            $expected = get_string($stringid, 'local_lionai_reports', ['code' => 500, 'detail' => 'Boom']);
        } else {
            $expected = get_string($stringid, 'local_lionai_reports');
        }
        $this->assertSame($expected, $message);
    }
}
