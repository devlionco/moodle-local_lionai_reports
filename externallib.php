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
 * @since      Moodle 3.9
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/lionai_reports/locallib.php');
require_once($CFG->dirroot . '/local/lionai_reports/clasess/lionaireportsapi.php');

/**
 * External tool module external functions
 *
 * @package    local_lionai_reports
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
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
            throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
            return;
        }

        $params = self::validate_parameters(self::getlist_parameters(),
            []);

        $context = context_system::instance();

        $list = local_lionai_reports_getlist($USER->id);

        $result = true;
        $data = new stdClass;
        $data->list = array_values($list);

        $response = new stdClass;
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
            throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
            return;
        }

        $params = self::validate_parameters(self::getreport_parameters(),
            []);

        $context = context_system::instance();

        $report = local_lionai_reports_getreport($id);

        // Decode the JSON data into a PHP array.
        $data = json_decode($report->options, true) ?? [];
        $data['history'] = $data['history'] ?? null;
        $lastmessages = local_lionai_reports_getlast_messages($data['history']); // Pass your history data as $data.
        $lastusercontent = $lastmessages['lastusercontent'];
        $lastassistantcontent = $lastmessages['lastassistantcontent'];

        $result = true;
        $data = new stdClass;

        $report->lastusercontent = $lastusercontent;
        $report->lastassistantcontent = $lastassistantcontent;

        $report->timecreated = userdate($report->timecreated, get_string('strftimedaydatetime'));
        $report->timemodified = userdate($report->timemodified, get_string('strftimedaydatetime'));

        $data->report = $report;

        $response = new stdClass;
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
            throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
            return;
        }

        $params = self::validate_parameters(self::getresult_parameters(),
            []);

        $context = context_system::instance();

        // Put response to history.
        $historyitem = new stdClass;
        $historyitem->role = 'assistant';
        $historyitem->content = $query;
        local_lionai_reports_put_history($reportid, $historyitem);

        list($status, $message, $resultdata) = local_lionai_reports_getresult($query);

        $result = $status;

        if (empty($resultdata) && empty($message)) {
            $message = get_string('no_data_found', 'local_lionai_reports');
        }

        $response = new stdClass;
        $response->result = $result;
        $response->message = $message;
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
            throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
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

        $report = local_lionai_reports_getreport($reportid);

        $options = json_decode($report->options);

        // Put userpromt to history.
        $historyitem = new stdClass;
        $historyitem->role = 'user';
        $historyitem->content = $prompt;
        local_lionai_reports_put_history($reportid, $historyitem);

        $key = get_config('local_lionai_reports', 'lionai_reports_apikey');
        $url = get_config('local_lionai_reports', 'lionai_reports_apiurl');
        $lionaireports = new local_lionai_reports\lionai_reports_api($key, $url);
        $response = $lionaireports->send_prompt($prompt);

        // Put response to history.
        $historyitem = new stdClass;
        $historyitem->role = 'assistant';
        if ($response[0] == 'Client is not eligible.' && $response[1] == 0) {
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
     * @return external_description
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
            throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
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

        $key = get_config('local_lionai_reports', 'lionai_reports_apikey');
        $url = get_config('local_lionai_reports', 'lionai_reports_apiurl');
        $lionaireports = new local_lionai_reports\lionai_reports_api($key, $url);
        $response = $lionaireports->rate_prompt($promptid, $rate);

        return [
            'message' => $response[0],
            'correct' => $response[1],
            'promptid' => $response[2],
        ];
    }

    /**
     * rate_prompt return
     *
     * @return external_description
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
            throw new \moodle_exception(get_string('permission_require', 'local_lionai_reports'));
            return;
        }

        $params = self::validate_parameters(self::updatereport_parameters(),
            [
                'id' => $id,
                'action' => $action,
                'data' => $data,
            ]);

        $context = context_system::instance();

        $result = local_lionai_reports_updatereport($id, $action, $data);

        $response = new stdClass;
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
