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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_lionai_reports\lionai_reports_api;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/lionai_reports/locallib.php');

/**
 * External tool module external functions
 *
 * @package    local_lionai_reports
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.1
 */
class local_lionai_reports_external extends external_api {
    /**
     * Describes the parameters for getlist
     * @return external_function_parameters
     */
    public static function getlist_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Fetches a list of LionAI reports
     * @return array of response data
     */
    public static function getlist() {
        global $USER;
        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
            return;
        }

        $params = self::validate_parameters(
            self::getlist_parameters(),
            []
        );

        $context = context_system::instance();

        self::validate_context($context);

        $list = local_lionai_reports_getlist($USER->id);

        $result = true;
        $data = new stdClass();
        $data->list = array_values($list);
        $data->sesskey = sesskey();

        $response = new stdClass();
        $response->result = $result;
        $response->data = json_encode($data);

        return $response;
    }

    /**
     * Describes the getlist return value.
     *
     * @return external_single_structure
     */
    public static function getlist_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'data' => new external_value(PARAM_RAW, 'data'),
        ]);
    }

    /**
     * Describes the parameters for getreport
     * @return external_function_parameters
     */
    public static function getreport_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'report id', VALUE_DEFAULT, 0),
            ]
        );
    }

    /**
     * Fetches a LionAI report
     * @param int $id id of the report
     * @return array of response data
     */
    public static function getreport($id) {
        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
            return;
        }

        $params = self::validate_parameters(
            self::getreport_parameters(),
            ['id' => $id]
        );
        $id = $params['id'];

        $context = context_system::instance();

        self::validate_context($context);

        $report = local_lionai_reports_getreport($id);

        // The options were already decoded by local_lionai_reports_getreport().
        $lastusercontent = $report->lastmessages['lastusercontent'];
        $lastassistantcontent = $report->lastmessages['lastassistantcontent'];

        $history = array_map(static function ($item) {
            $item = (array) $item;
            unset($item['classes']); // Presentation data added by local_lionai_reports_getreport().
            return $item;
        }, $report->history);
        $result = true;
        $data = new stdClass();

        $report->lastusercontent = $lastusercontent;
        $report->lastassistantcontent = $lastassistantcontent;

        $report->timecreated = userdate($report->timecreated, get_string('strftimedaydatetime'));
        $report->timemodified = userdate($report->timemodified, get_string('strftimedaydatetime'));

        $data->report = $report;
        $organizehistory = [];
        $newlog = null;
        foreach ($history as $log) {
            if ($log['role'] == 'user') {
                if (!empty($newlog)) {
                    $organizehistory[] = $newlog;
                }
                $newlog = [];
                $newlog['prompt'] = $log;
            }
            if ($log['role'] == 'assistant') {
                $newlog['code'][] = $log;
            }
        }
        if (!empty($newlog)) {
            $organizehistory[] = $newlog;
        }
        $data->report->history = $organizehistory;

        $response = new stdClass();
        $response->result = $result;
        $response->data = json_encode($data);

        return $response;
    }

    /**
     * Describes the getreport return value.
     *
     * @return external_single_structure
     */
    public static function getreport_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'data' => new external_value(PARAM_RAW, 'data'),
        ]);
    }

    /**
     * Describes the parameters for save_sql
     * @return external_function_parameters
     */
    public static function save_sql_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'report id'),
                'query' => new external_value(PARAM_RAW, 'query'),
            ]
        );
    }

    /**
     * Stores the SQL the user edited manually in the report history (marked as manual, not as an AI answer).
     *
     * @param int $id id of the report
     * @param string $query the SQL
     * @return stdClass response
     */
    public static function save_sql($id, $query) {
        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
        }

        $params = self::validate_parameters(self::save_sql_parameters(), ['id' => $id, 'query' => $query]);

        self::validate_context(context_system::instance());

        $result = false;
        if (local_lionai_reports_is_select_only($params['query'])) {
            $historyitem = new stdClass();
            $historyitem->role = 'assistant';
            $historyitem->content = $params['query'];
            $historyitem->manual = 1;
            $result = (bool) local_lionai_reports_put_history($params['id'], $historyitem);
        }

        $response = new stdClass();
        $response->result = $result;

        return $response;
    }

    /**
     * Describes the save_sql return value.
     *
     * @return external_single_structure
     */
    public static function save_sql_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Whether the SQL was stored'),
        ]);
    }

    /**
     * Describes the parameters for getresult
     * @return external_function_parameters
     */
    public static function getresult_parameters() {
        return new external_function_parameters(
            [
                'query' => new external_value(PARAM_RAW, 'query', VALUE_DEFAULT, ''),
                'id' => new external_value(PARAM_INT, 'report id', VALUE_DEFAULT, 0),
            ]
        );
    }

    /**
     * Fetches the results of sql query from a LionAI report
     * @param string $query query the report
     * @param int $reportid id of the report
     * @return array of response data
     */
    public static function getresult($query, $reportid) {
        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
            return;
        }

        $params = self::validate_parameters(
            self::getresult_parameters(),
            ['query' => $query, 'id' => $reportid]
        );
        $query = $params['query'];
        $reportid = $params['id'];

        $context = context_system::instance();

        self::validate_context($context);

        // This is a read-only service: it must not write to the database (see save_sql() for storing edited SQL).

        [$status, $message, $resultdata, $countrsults] = local_lionai_reports_getresult($query);

        $result = $status;

        if (empty($resultdata) && empty($message)) {
            $message = get_string('no_data_found', 'local_lionai_reports');
        }

        $response = new stdClass();
        $response->result = $result;
        $response->message = $message;
        $response->count = $countrsults;
        $response->data = json_encode($resultdata);

        return $response;
    }

    /**
     * Describes the getresult return value.
     *
     * @return external_single_structure
     */
    public static function getresult_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_INT, 'The processing result'),
            'message' => new external_value(PARAM_RAW, 'The processing message'),
            'count' => new external_value(PARAM_INT, 'count results'),
            'data' => new external_value(PARAM_RAW, 'data'),
        ]);
    }

    /**
     * send_prompt parameters.
     * @return external_function_parameters
     */
    public static function send_prompt_parameters() {
        return new external_function_parameters(
            [
                'reportid' => new external_value(PARAM_INT, 'The report id', VALUE_REQUIRED),
                'prompt' => new external_value(PARAM_RAW, 'Prompt', VALUE_REQUIRED),
                'engine' => new external_value(PARAM_ALPHANUMEXT, 'engine', VALUE_OPTIONAL),
                'promptid' => new external_value(PARAM_ALPHANUMEXT, 'promptid', VALUE_OPTIONAL),
            ]
        );
    }

    /**
     * Returns data of given report id.
     *
     * @param int $reportid
     * @param string $prompt
     * @param mixed $engine
     * @param int $promptid
     * @return array An array with a 'data' JSON string and a 'warnings' string
     */
    public static function send_prompt($reportid, $prompt, $engine = null, $promptid = null) {
        global $CFG, $DB;

        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
            return;
        }

        $params = self::validate_parameters(
            self::send_prompt_parameters(),
            [
                'reportid' => $reportid,
                'prompt' => $prompt,
                'engine' => $engine,
                'promptid' => $promptid,
            ]
        );

        $context = context_system::instance();

        self::validate_context($context);

        if (count(preg_split('/\s+/', trim($prompt), -1, PREG_SPLIT_NO_EMPTY)) < 2) {
            throw new \moodle_exception('promptminwords', 'local_lionai_reports');
        }

        // Put userpromt to history.
        $historyitem = new stdClass();
        $historyitem->role = 'user';
        $historyitem->content = $prompt;
        local_lionai_reports_put_history($reportid, $historyitem);

        $key = (string) get_config('local_lionai_reports', 'lionai_reports_apikey');
        $url = (string) get_config('local_lionai_reports', 'lionai_reports_apiurl');
        $lionaireports = new lionai_reports_api($key, $url);
        $response = $lionaireports->send_prompt($prompt);

        // Put response to history.
        $historyitem = new stdClass();
        $historyitem->role = 'assistant';
        $pattern = '/not eligible/i'; // The 'i' flag makes the search case-insensitive.

        if (preg_match($pattern, $response[0]) && $response[1] == 0) {
            $response[0] = get_string('not_eligible_message', 'local_lionai_reports');
        }
        $historyitem->content = $response[0];
        local_lionai_reports_put_history($reportid, $historyitem);

        return [
            'message' => $response[0],
            'correct' => $response[1],
            'promptid' => $response[2],
        ];
    }

    /**
     * send_prompt return
     *
     * @return external_single_structure
     */
    public static function send_prompt_returns() {
        return new external_single_structure(
            [
                'message' => new external_value(PARAM_RAW, 'SQL response'),
                'correct' => new external_value(PARAM_INT, 'correct'),
                'promptid' => new external_value(PARAM_ALPHANUMEXT, 'promptid'),
            ]
        );
    }

    /**
     * rate_prompt parameters.
     *
     * @return external_function_parameters
     */
    public static function rate_prompt_parameters() {
        return new external_function_parameters(
            [
                'promptid' => new external_value(PARAM_INT, 'promptid', VALUE_REQUIRED),
                'rate' => new external_value(PARAM_INT, 'Rate', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Returns data of given report id.
     *
     * @param int $promptid
     * @param int $rate
     * @return array An array with a 'data' JSON string and a 'warnings' string
     */
    public static function rate_prompt($promptid, $rate) {
        global $CFG, $DB;
        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
            return;
        }

        $params = self::validate_parameters(
            self::rate_prompt_parameters(),
            [
                'promptid' => $promptid,
                'rate' => $rate,
            ]
        );

        $context = context_system::instance();

        self::validate_context($context);

        $key = (string) get_config('local_lionai_reports', 'lionai_reports_apikey');
        $url = (string) get_config('local_lionai_reports', 'lionai_reports_apiurl');
        $lionaireports = new lionai_reports_api($key, $url);
        $response = $lionaireports->rate_prompt($params['promptid'], $params['rate']);

        return [
            'message' => (string) $response[0],
            'correct' => $response[1],
            'promptid' => (string) $params['promptid'],
        ];
    }

    /**
     * rate_prompt return
     *
     * @return external_single_structure
     */
    public static function rate_prompt_returns() {
        return new external_single_structure(
            [
                'message' => new external_value(PARAM_RAW, 'SQL response'),
                'correct' => new external_value(PARAM_INT, 'correct'),
                'promptid' => new external_value(PARAM_ALPHANUMEXT, 'promptid'),
            ]
        );
    }

    /**
     * updatereport parameters.
     *
     * @return external_function_parameters
     */
    public static function updatereport_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'report id', VALUE_DEFAULT, 0),
                'action' => new external_value(PARAM_ALPHANUM, 'action', VALUE_DEFAULT, 'update'),
                'data' => new external_value(PARAM_RAW, 'data'),
            ]
        );
    }

    /**
     * Updates the report.
     *
     * @param int $id
     * @param string $action
     * @param mixed $data
     * @return array the response with data about the update status
     */
    public static function updatereport($id, $action, $data) {

        if (!is_siteadmin()) {
            throw new \moodle_exception('permission_require', 'local_lionai_reports');
            return;
        }

        $params = self::validate_parameters(
            self::updatereport_parameters(),
            [
                'id' => $id,
                'action' => $action,
                'data' => $data,
            ]
        );

        $context = context_system::instance();

        self::validate_context($context);

        $result = local_lionai_reports_updatereport($id, $action, $data);

        $response = new stdClass();
        $response->result = $result;

        return $response;
    }

    /**
     * Describes the updatereport return value.
     *
     * @return external_single_structure
     */
    public static function updatereport_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
        ]);
    }
}
