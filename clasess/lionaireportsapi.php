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

/**
 * class of connecting to API of an AI
 */
class lionai_reports {

    /** @var string */
    private $key;

    /** @var string */
    private $url;

    /** @var string */
    private $error;

    /** @var string */
    private $message;

    /** @var string */
    private $action;

    /** @var int */
    private $promptid;

    /**
     * Construct function
     *
     * @param string $key
     * @param string $url
     * @param string $action
     */
    public function __construct($key, $url, $action = 'get_query') {
        $this->key = $key;
        $this->url = $url;
        $this->action = $action;
    }

    /**
     * send_prompt
     *
     * @param string $prompt
     */
    public function send_prompt($prompt) {

        $promptobj = $this->prepare_prompt($prompt);

        $response = $this->send_request($promptobj);
        if ($this->error) {
            return [$this->message, 0];
        }

        return $this->process_sql_response($response);
    }

    /**
     * rate_prompt
     *
     * @param int $promptid
     * @param int $rate
     */
    public function rate_prompt($promptid, $rate) {

        $promptobj = $this->prepare_rate($promptid, $rate);

        $response = $this->send_request($promptobj);
        if ($this->error) {
            return [$this->message, 0];
        }

        return $this->process_rate_response($response);
    }

    /**
     * prepare_prompt
     *
     * @param string $prompt
     */
    private function prepare_prompt($prompt) {
        $promptobj = [];
        $promptobj['prompt'] = $prompt;
        $promptobj['action'] = $this->action;
        $promptobj['key'] = $this->key;
        return $promptobj;
    }

    /**
     * prepare_rate
     *
     * @param int $promptid
     * @param int $rate
     */
    private function prepare_rate($promptid, $rate) {
        $promptobj = [];
        $promptobj['promptid'] = $promptid;
        $promptobj['rating'] = $rate;
        $promptobj['action'] = $this->action;
        $promptobj['key'] = $this->key;
        return $promptobj;
    }

    /**
     * Error handling
     */
    private function has_error() {
        // TODO: Error handling.
        return false;
    }

    /**
     * process_sql_response
     *
     * @param array $response
     */
    private function process_sql_response($response) {

        $response = json_decode($response);
        $this->message = str_replace('\n', "\n", $response->Message);
        $this->promptid = $response->promptid;

        if (!$response->error) {
            return self::extract_sql($this->message);
        } else {
            return [$this->message, 0, $this->promptid];
        }
    }

    /**
     * process_rate_response
     *
     * @param array $response
     */
    private function process_rate_response($response) {

        $response = json_decode($response);

        if (isset($response->error) && $response->error) {
            $this->message = $response->error;
            return [$this->message, 0];
        } else {
            $this->message = $response->success;
            return [$this->message, 2];
        }
    }

    /**
     * extract_sql
     *
     * @param string $rawinput
     */
    public function extract_sql($rawinput) {
        $correct = 0;
        $sql = '';

        if (preg_match('/^\s*SELECT\b/i', $rawinput)) {
            $correct = 2;
            $sql = $rawinput;
        } else {
            if (preg_match('/```(sql)?(.*?)```/s', $rawinput, $matches)) {
                $sql = trim($matches[2]);
                if (self::is_valid_sql($sql)) {
                    $correct = 2;
                }
            } else {
                if (preg_match('/\bSELECT[^;]*(?:FROM|JOIN|GROUP BY|ORDER BY|UNION)\b[^;]*;/si', $rawinput, $matches)) {
                    $sql = trim($matches[0]);
                    if (self::is_valid_sql($sql)) {
                        $correct = 2;
                    }
                }
            }
        }

        return [rtrim($sql, ';'), $correct, $this->promptid];
    }

    /**
     * is_valid_sql
     *
     * @param string $sql
     */
    public function is_valid_sql($sql) {
        return (stripos($sql, 'SELECT') !== false && stripos($sql, 'FROM') !== false);
    }

    /**
     * send_request to API
     *
     * @param string $data
     */
    private function send_request($data) {

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ];
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errormessage = curl_error($ch);
            curl_close($ch);
            $this->error = 1;
            $this->message = "cURL Error: $errormessage";
        }
        curl_close($ch);

        return $response;
    }

}
