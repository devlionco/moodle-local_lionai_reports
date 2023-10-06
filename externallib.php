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
 * @package    local_smartreport
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.9
 */

defined('MOODLE_INTERNAL') || die;

require_once $CFG->libdir . '/externallib.php';
require_once $CFG->dirroot . '/local/smartreport/locallib.php';

/**
 * External tool module external functions
 *
 * @package    local_smartreport
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class local_smartreport_external extends external_api {

    public static function getlist_parameters() {
        return new external_function_parameters(
            array(
                // 'orphanedonly' => new external_value(PARAM_BOOL, 'Orphaned tool types only', VALUE_DEFAULT, 0)
            )
        );
    }

    public static function getlist() {
        global $USER;

        $params = self::validate_parameters(self::getlist_parameters(),
            array(
            ));

        $context = context_system::instance();

        $list = local_smartreport_getlist($USER->id);

        $result = true;
        $data = new stdClass;
        $data->list = array_values($list);

        $response = new stdClass;
        $response->result = $result;
        $response->data = json_encode($data);

        return $response;
    }

    public static function getlist_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'data' => new external_value(PARAM_RAW, 'data'),
        ]);
    }

    public static function getreport_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'report id', VALUE_DEFAULT, 0),
            )
        );
    }

    public static function getreport($id) {
        $params = self::validate_parameters(self::getreport_parameters(),
            array(
            ));

        $context = context_system::instance();

        $report = local_smartreport_getreport($id);

        $result = true;
        $data = new stdClass;
        $data->report = $report;

        $response = new stdClass;
        $response->result = $result;
        $response->data = json_encode($data);

        return $response;
    }

    public static function getreport_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'data' => new external_value(PARAM_RAW, 'data'),
        ]);
    }

    public static function getresult_parameters() {
        return new external_function_parameters(
            array(
                'query' => new external_value(PARAM_RAW, 'query', VALUE_DEFAULT, ''),
            )
        );
    }

    public static function getresult($query) {
        $params = self::validate_parameters(self::getresult_parameters(),
            array(
            ));

        $context = context_system::instance();

        list($status, $message, $resultdata) = local_smartreport_getresult($query);

        $result = $status;
        // $data = new stdClass;
        // $data->resultdata = $resultdata;

        $response = new stdClass;
        $response->result = $result;
        $response->message = $message;
        $response->data = json_encode($resultdata);

        return $response;
    }

    public static function getresult_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'message' => new external_value(PARAM_RAW, 'The processing message'),
            'data' => new external_value(PARAM_RAW, 'data'),
        ]);
    }

    /**
     * send_prompt parameters.
     *
     * @return external_function_parameters
     */
    public static function send_prompt_parameters() {
        return new external_function_parameters(
            array(
                'reportid' => new external_value(PARAM_INT, 'The report id', VALUE_REQUIRED),
                'prompt' => new external_value(PARAM_TEXT, 'Prompt', VALUE_REQUIRED),
                'engine' => new external_value(PARAM_ALPHANUMEXT, 'engine', VALUE_OPTIONAL),
                'conversationid' => new external_value(PARAM_ALPHANUMEXT, 'conversationid', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Returns data of given report id.
     *
     * @param int $reportid
     * @param int $courseid
     * @return array An array with a 'data' JSON string and a 'warnings' string
     */
    public static function send_prompt($reportid, $prompt, $engine = null, $conversationid = null) {
        global $CFG, $DB;

        $params = self::validate_parameters(
            self::send_prompt_parameters(),
            [
                'reportid' => $reportid,
                'prompt' => $prompt,
                'engine' => $engine,
                'conversationid' => $conversationid,
            ]
        );

        // if ($courseid == SITEID) {
        $context = context_system::instance();
        // } else {
        //     $context = context_course::instance($courseid);
        // }

        self::validate_context($context);

        $report = local_smartreport_getreport($reportid);

        // $gptx = new gptx_prompt($reportid);
        // $gptx->set_model($model);
        // $response = $gptx->send_prompt($prompt);

        $options = json_decode($report->options);

        if ($options->engine == '') {
            $response[0] = 'SELECT * FROM {course}';
            $response[1] = true;
            $response[2] = 'RNDM-1234';
        } else {
            // TODO: Query to Devlion BlackBox API
        }

        return [
            'message' => $response[0],
            'correct' => $response[1],
            'conversationid' => $response[2],
        ];
    }

    /**
     * send_prompt return
     *
     * @return external_description
     */
    public static function send_prompt_returns() {
        return new external_single_structure(
            array(
                'message' => new external_value(PARAM_RAW, 'SQL response'),
                'correct' => new external_value(PARAM_BOOL, 'correct'),
                'conversationid' => new external_value(PARAM_ALPHANUMEXT, 'conversationid'),
            )
        );
    }
}
