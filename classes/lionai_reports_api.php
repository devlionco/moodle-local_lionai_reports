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
namespace local_lionai_reports;

/**
 * Client of the LionAI API (generates SQL from natural language prompts and rates the answers).
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lionai_reports_api {
    /** @var string */
    private $key;

    /** @var string */
    private $url;

    /** @var int Non zero when the last request failed at cURL level. */
    private $error = 0;

    /** @var string */
    private $message = '';

    /** @var string */
    private $action;

    /** @var string */
    private $promptid = '';

    /** @var int HTTP status code of the last request. */
    private $httpcode = 0;

    /** @var string Raw body of the last request. */
    private $rawresponse = '';

        /**
         * Constructor.
         *
         * @param string $key The API key.
         * @param string $url The API endpoint URL.
         * @param string $action The action sent to the legacy API.
         */
    public function __construct(string $key, string $url, string $action = 'reportAction') {
        $this->key = $key;
        $this->url = $url;
        $this->action = $action;
    }

        /**
         * Sends a prompt to the API and returns the extracted SQL.
         *
         * @param string $prompt The user prompt.
         * @return array [string sql or error message, int correct (2 = valid SQL), string prompt id]
         */
    public function send_prompt(string $prompt): array {

        $promptobj = $this->prepare_prompt($prompt);

        $response = $this->send_request($promptobj);

        if ($this->error) {
            return [$this->message, 0];
        }
        if ($this->httpcode >= 400) {
            return [$this->http_error_message(), 0, ''];
        }

        return $this->process_sql_response($response);
    }

    /**
     * Builds the message for a failed HTTP request. A "Forbidden" answer means the API key is not valid.
     *
     * @return string
     */
    private function http_error_message(): string {
        $decoded = json_decode($this->rawresponse);
        if ($this->httpcode === 403 && is_object($decoded) && ($decoded->message ?? '') === 'Forbidden') {
            return get_string('invalidapikey', 'local_lionai_reports');
        }

        // A wrong URL (the gateway does not know the path) is not a key problem.
        if (
            in_array($this->httpcode, [403, 404], true) && is_object($decoded)
                && in_array($decoded->message ?? '', ['Missing Authentication Token', 'Not Found'], true)
        ) {
            return get_string('invalidapiurl', 'local_lionai_reports');
        }

        $detail = \core_text::substr(trim(strip_tags($this->rawresponse)), 0, 200);
        return get_string(
            'testconnection_httperror',
            'local_lionai_reports',
            ['code' => $this->httpcode ?: '-', 'detail' => $detail]
        );
    }

    /**
     * Tests the connection to the API using a minimal request.
     *
     * @return array [bool success, string message]
     */
    public function test_connection(): array {
        $this->error = 0;
        $this->message = '';
        $response = $this->send_request($this->prepare_prompt('test connection'));

        // Network / cURL level failure (DNS, timeout, SSL, refused...).
        if ($this->error) {
            return [false, $this->message];
        }

        // HTTP level failure (wrong key, wrong URL, server error).
        if ($this->httpcode >= 400 || $this->httpcode === 0) {
            return [false, $this->http_error_message()];
        }

        // The service answered, but not with the expected format (usually a wrong URL).
        if (!is_object($response) || !isset($response->message)) {
            $detail = \core_text::substr(trim(strip_tags($this->rawresponse)), 0, 200);
            return [false, get_string('testconnection_badresponse', 'local_lionai_reports', $detail)];
        }

        // The service answered with an error (invalid key, quota exceeded...).
        if (!empty($response->iserror)) {
            return [false, (string) $response->message];
        }

        return [true, ''];
    }

        /**
         * Sends a rating of a previous answer to the API.
         *
         * @param int $promptid The id of the prompt, as returned by the API.
         * @param int $rate The rating (1 or -1).
         * @return array [string message, int status (2 = success)]
         */
    public function rate_prompt(int $promptid, int $rate): array {

        $promptobj = $this->prepare_rate($promptid, $rate);

        $response = $this->send_request($promptobj);
        if ($this->error) {
            return [$this->message, 0];
        }
        if ($this->httpcode >= 400) {
            return [$this->http_error_message(), 0];
        }

        return $this->process_rate_response($response);
    }

        /**
         * Builds the JSON data (Moodle version, DB type and the request) sent to the API.
         *
         * @param string $prompt The user prompt.
         * @return string JSON encoded data
         */
    private function prepare_data(string $prompt): string {
        global $CFG, $DB;

        require("$CFG->dirroot/version.php");
        require_once($CFG->libdir . '/environmentlib.php');

        $data = [];
        $data['moodle_ver'] = normalize_version($release);
        $data['db_type'] = $CFG->dbtype;
        $data['sql_request'] = $prompt;

        return json_encode($data);
    }

        /**
         * Builds the request body of a prompt.
         *
         * @param string $prompt The user prompt.
         * @return array The request body
         */
    private function prepare_prompt(string $prompt): array {
        $promptobj = [];
        $promptobj['data'] = $this->prepare_data($prompt);
        $promptobj['action'] = $this->action;
        $promptobj['token'] = $this->key;
        return $promptobj;
    }

        /**
         * Builds the request body of a rating.
         *
         * @param int $promptid The id of the prompt.
         * @param int $rate The rating (1 or -1).
         * @return array The request body
         */
    private function prepare_rate(int $promptid, int $rate): array {
        $promptobj = [];
        $promptobj['promptid'] = $promptid;
        $promptobj['rating'] = $rate;
        $promptobj['action'] = $this->action;
        $promptobj['token'] = $this->key;
        return $promptobj;
    }

        /**
         * Processes the response of a prompt request.
         *
         * @param object|null $response The decoded response, as returned by send_request().
         * @return array [string sql or error message, int correct, string prompt id]
         */
    private function process_sql_response(?object $response): array {

        if (!is_object($response) || !isset($response->message)) {
            $this->error = 1;
            $this->message = get_string('invalidapiresponse', 'local_lionai_reports');
            return [$this->message, 0, ''];
        }

        $this->message = str_replace('\n', "\n", $response->message);
        $this->promptid = $response->promptid ?? '';

        if (empty($response->iserror)) {
            return self::extract_sql($this->message);
        } else {
            return [$this->message, 0, $this->promptid];
        }
    }

        /**
         * Processes the response of a rating request.
         *
         * @param object|null $response The decoded response, as returned by send_request().
         * @return array [string message, int status (2 = success)]
         */
    private function process_rate_response(?object $response): array {

        // The response was already decoded by send_request().
        if (!is_object($response)) {
            $this->message = get_string('invalidapiresponse', 'local_lionai_reports');
            return [$this->message, 0];
        }

        if (isset($response->error) && $response->error) {
            $this->message = $response->error;
            return [$this->message, 0];
        } else {
            $this->message = $response->success ?? '';
            return [$this->message, 2];
        }
    }

        /**
         * Extracts the SQL out of the raw text answered by the AI.
         *
         * @param string $rawinput The raw answer.
         * @return array [string sql, int correct (2 = valid SQL), string prompt id]
         */
    public function extract_sql(string $rawinput): array {
        $correct = 0;
        $sql = '';

        if (preg_match('/^\s*SELECT\b/i', $rawinput)) {
            $correct = 2;
            $sql = $rawinput;
        } else {
            if (preg_match('/' . str_repeat(chr(96), 3) . '(sql)?(.*?)' . str_repeat(chr(96), 3) . '/s', $rawinput, $matches)) {
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
         * Basic check that the text looks like a SELECT query.
         *
         * @param string $sql The SQL.
         * @return bool
         */
    public function is_valid_sql(string $sql): bool {
        return (stripos($sql, 'SELECT') !== false && stripos($sql, 'FROM') !== false);
    }

        /**
         * Sends the request to the API.
         *
         * @param array $data The request body.
         * @return object|null The decoded response, or null when it could not be decoded
         */
    private function send_request(array $data): ?object {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $ch = new \curl();
        $response = null;

        // Determine if using the new API.
        $isnewapi = !(strpos($this->url, 'LionAI') !== false);

        if ($isnewapi) {
            $headers = [
                'x-api-key: ' . $this->key,
            ];
            $ch->setHeader($headers);

            $response = $ch->post($this->url, json_encode($data));
            $this->rawresponse = (string) $response;
            $response = json_decode($response);
            $response = is_object($response) && isset($response->body) ? json_decode($response->body) : null;
        } else {
            $response = $ch->post($this->url, json_encode($data));
            $this->rawresponse = (string) $response;
            $response = json_decode($response);
        }

        $info = $ch->get_info();
        $this->httpcode = (int) ($info['http_code'] ?? 0);

        if ($ch->get_errno()) {
            $errormessage = $ch->error;
            $this->error = 1;
            $this->message = get_string('curlerror', 'local_lionai_reports', $errormessage);
        }

        return $response;
    }
}
