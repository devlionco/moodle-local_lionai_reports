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

namespace local_lionai_reports\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_lionai_reports\lionai_reports_api;

/**
 * External function testing the connection to the LionAI API.
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_connection extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'apikey' => new external_value(PARAM_RAW, 'API key (empty to use the saved one)', VALUE_DEFAULT, ''),
            'apiurl' => new external_value(PARAM_RAW, 'API URL (empty to use the saved one)', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Tests the connection with the given (or saved) API settings.
     *
     * @param string $apikey
     * @param string $apiurl
     * @return array
     */
    public static function execute(string $apikey = '', string $apiurl = ''): array {
        $params = self::validate_parameters(self::execute_parameters(), ['apikey' => $apikey, 'apiurl' => $apiurl]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/lionai_reports:manage', $context);

        $key = trim($params['apikey']);
        if ($key === '') {
            $key = (string) get_config('local_lionai_reports', 'lionai_reports_apikey');
        }
        $url = trim($params['apiurl']);
        if ($url === '') {
            $url = (string) get_config('local_lionai_reports', 'lionai_reports_apiurl');
        }

        if ($key === '') {
            return ['success' => false, 'message' => get_string('testconnection_nokey', 'local_lionai_reports')];
        }
        if ($url === '') {
            return ['success' => false, 'message' => get_string('testconnection_nourl', 'local_lionai_reports')];
        }

        if (!preg_match('#^https?://[^\s/]+#i', $url) || clean_param($url, PARAM_URL) === '') {
            return ['success' => false, 'message' => get_string('testconnection_invalidurl', 'local_lionai_reports', s($url))];
        }

        $api = new lionai_reports_api($key, $url);
        [$success, $message] = $api->test_connection();

        return [
            'success' => $success,
            'message' => $success ? get_string('testconnection_ok', 'local_lionai_reports') : $message,
        ];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the connection works'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }
}
