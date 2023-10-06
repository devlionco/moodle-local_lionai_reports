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
 * class gptx_prompt.
 *
 * @module      block_conf_reports_gptx/main
 * @author      Anton P. <anton@devlion.co>
 * @copyright   2023 Devlion <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

namespace block_conf_reports_gptx;

use stdClass;

defined('MOODLE_INTERNAL') || die();

// require_once '../../../config.php';
require_once $CFG->libdir . '/filelib.php';

class gptx_prompt {

    private $reportid;
    private $key;
    private $url;
    private $history = '';
    private $result = '';
    private $curl;
    private $model;

    // TODO: refactor.

    // TODO: Model dropdown.
    // gpt-3.5-turbo
    // "model": "gpt-4",
    // "model": "gpt-3.5-turbo",
    private $initparams = '
    {
        "temperature": 0,
        "max_tokens": 1024,
        "top_p": 1,
        "frequency_penalty": 0,
        "presence_penalty": 0,
        "messages": [
            {
                "role": "system",
                "content": "As the Moodle SQL assistant, your task is to generate accurate SQL code in response to human-readable requests (e.g., \"get all users\"). Follow these guidelines: Start with \"SELECT\" statement. Exclude all additional text. If generating SQL isnt possible, return \"null.\" Avoid table name prefixes (e.g., \"mdl_\"). Omit the \"LIMIT\" keyword. Use curly braces ({}) to enclose table names \"FROM {user}\". All SQL keywords are in UPPER CASE. Complex SQL queries should be on multiple lines. Multiline SQL queries should be right aligned on SELECT, FROM, JOIN, WHERE, GROUPY BY and HAVING. Use JOIN instead of INNER JOIN. Do not use right joins. Always use AS keyword for column aliases. Never use AS keyword for table aliases. Use <> for comparing if values are not equals and do not use !=. Most used tables: user,course,assignment,quiz,forum,lesson,grade_items,grade_grades,question,modules,messages,resource,course_modules. Your role is to provide valid SQL code per these rules."
            }
            ]
    }
    ';

// All SQL keywords are in UPPER CASE. All SQL queries and fragments should be enclosed in double quotes. Complex SQL queries should be on multiple lines. Multiline SQL queries should be right aligned on SELECT, FROM, JOIN, WHERE, GROUPY BY and HAVING. Use JOIN instead of INNER JOIN. Do not use right joins. Always use AS keyword for column aliases. Never use AS keyword for table aliases. Use <> for comparing if values are not equals and do not use !=.

    // As the Moodle SQL assistant, your task is to generate accurate SQL code in response to human-readable requests (e.g., \"get all users\"). Follow these guidelines: Start with \"SELECT\" statement. Exclude all additional text. If generating SQL isnt possible, return \"null.\" Avoid table name prefixes (e.g., \"mdl_\"). Omit the \"LIMIT\" keyword. Use curly braces ({}) to enclose table names. Employ aliases for tables and columns as needed. Your role is to provide valid SQL code per these rules.

    // You are the dedicated SQL assistant for Moodle. You will receive human-readable requests (e.g., \"get all users\"). Your sole responsibility is to generate accurate SQL code for Moodle without any additional text. If generating appropriate SQL code is not possible, return null. Do not use table name prefixes (e.g., \"mdl_\"). Do not use \"LIMIT\" keyword, leave sql without limiting. Enclose table names within curly braces ({}). Utilize aliases for tables and selected columns as needed.

    public function __construct($reportid) {
        $this->reportid = $reportid;
        $this->key = get_config('block_conf_reports_gptx', 'openai_apikey');
        $this->url = get_config('block_conf_reports_gptx', 'openai_url');
        $this->curl = new \curl();
        $this->model = 0;
    }

    public function get_history() {
        global $DB;

        $report = $this->get_report();

        return json_decode($report->gptxhistory) ?? null;
    }

    public function set_model($model) {
        if ($model == 0) {
            $this->model = 'gpt-3.5-turbo';
        } else {
            $this->model = 'gpt-4';
        }
    }

    public function put_history($history) {
        global $DB;

        $report = $this->get_report();
        $report->gptxhistory = json_encode($history);
        $response = $DB->update_record('block_conf_reports_gptx', $report);

        return $response;
    }

    public function get_report() {
        global $DB;

        $report = $DB->get_record('block_conf_reports_gptx', ['id' => $this->reportid]);

        return $report;
    }

    public function send_prompt($prompt) {

        // Set header.
        $header = [
            "Authorization: Bearer {$this->key}",
            'Content-Type: application/json',
        ];
        $this->curl->setHeader($header);

        // Prepare prompt.
        $promptObj = $this->prepare_prompt($prompt);

        // Get history and messages.
        $history = $this->get_history() ?? [];
        $initParams = json_decode($this->initparams);
        $initParams->model = $this->model;
        $messages = $initParams->messages;

        // Merge messages and history.
        // TODO: Make settings respons for including full history in request. Use only actual message by default.
        // $newMessages = array_merge($messages, $history, [$promptObj]);
        $newMessages = array_merge($messages, [$promptObj]);
        $history_and_prompt = array_merge($history, [$promptObj]);
        $initParams->messages = $newMessages;

        // Send request and get response.
        $response = $this->curl->post($this->url, json_encode($initParams));

        // Handle error.
        if ($this->has_error()) {
            echo 'ERROR';
            return;
        }

        // Process response.
        return $this->process_response($response, $history_and_prompt);
    }

    private function prepare_prompt($prompt) {
        $promptObj = new stdClass;
        $promptObj->role = 'user';
        $promptObj->content = $prompt;
        return $promptObj;
    }

    private function has_error() {
        $info = $this->curl->get_info();
        return $this->curl->error || $info['http_code'] !== 200;
    }

    private function process_response($response, $newMessages) {
        $respMessage = json_decode($response)->choices[0]->message;
        $this->result = $respMessage->content;

        $correct = true;

        preg_match('/SELECT.*$/s', $this->result, $matches);

        // Output the found SQL code.
        if ($matches) {
            if ($this->result != $matches[0]) {
                $correct = false;
            }
            $this->result = $matches[0];
        } else {
            $correct = false;
        }

        // Remove trailing semicolon,
        $this->result = \rtrim($this->result, ";");

        $newHistory = array_merge($newMessages, [$respMessage]);

        // Save history.
        $this->put_history($newHistory);

        return [$this->result, $correct];
    }

}
