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
 * LionAI Reports library code.
 *
 * @package    local_lionai_reports
 * @category   external
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Fetches a list of LionAI reports.
 *
 * @param int $userid The user ID to filter the reports (optional).
 *
 * @return array An array of LionAI reports.
 */
function local_lionai_reports_getlist(int $userid = 0): array {
    global $DB;

    $list = [];
    $table = 'local_lionai_reports';
    $conditions = [];

    if ($userid > 0 && get_config('local_lionai_reports', 'lionai_reports_allsee') && !is_siteadmin()) {
        $conditions['userid'] = $userid;
    }

    $sort = 'timemodified DESC';
    $records = $DB->get_records($table, $conditions, $sort);

    $crexists = local_lionai_reports_check_for_cr();

    // Load all the owners with a single query.
    $userids = array_filter(array_unique(array_column($records, 'userid')));
    $users = [];
    if ($userids) {
        $namefields = \core_user\fields::for_name()->get_required_fields();
        $users = $DB->get_records_list('user', 'id', $userids, '', 'id, ' . implode(', ', $namefields));
    }

    foreach ($records as $record) {
        $record->link = (new moodle_url('/local/lionai_reports/index.php', ['id' => $record->id]))->out();
        $record->userfullname = local_lionai_reports_ownername($record->userid, $users);
        $record->actions = new stdClass();
        $record->actions->sesskey = sesskey();
        $record->actions->deleteactionurl = (new moodle_url(
            '/local/lionai_reports/index.php',
            ['id' => $record->id, 'action' => 'delete']
        ))->out();
        $record->actions->exportcrautoactionurl = $crexists ? (new moodle_url(
            '/local/lionai_reports/',
            ['id' => $record->id, 'export' => 'confreports']
        ))->out() : '';
        $record->actions->exportcrxmlactionurl = (new moodle_url(
            '/local/lionai_reports/index.php',
            ['id' => $record->id, 'export' => 'confreports']
        ))->out();
        $record->actions->exportsqlactionurl = (new moodle_url(
            '/local/lionai_reports/index.php',
            ['id' => $record->id, 'export' => 'sqlformat']
        ))->out();
        $record->actions->exportcsvactionurl = (new moodle_url(
            '/local/lionai_reports/export.php',
            ['id' => $record->id]
        ))->out();

        $options = empty($record->options) ? ['history' => null] : json_decode($record->options, true);

        $record->lastmessages = local_lionai_reports_getlast_messages($options['history']);
        $record->actions->hasquery = trim((string) $record->lastmessages['lastassistantcontent']) !== '';

        $list[] = $record;
    }

    return $list;
}

/**
 * Returns the full name of a report owner, or an empty string if the owner no longer exists (e.g. anonymised).
 *
 * @param int $userid The owner user id.
 * @param array|null $users Optional, already loaded user records indexed by id (avoids a query per call).
 *
 * @return string
 */
function local_lionai_reports_ownername(int $userid, ?array $users = null): string {
    global $DB;

    if ($users !== null) {
        $user = $users[$userid] ?? false;
    } else {
        $user = $userid ? $DB->get_record('user', ['id' => $userid]) : false;
    }
    return $user ? fullname($user, true) : '';
}

/**
 * Fetches a single LionAI report by ID.
 *
 * @param int $id The ID of the report to fetch.
 *
 * @return object The LionAI report.
 */
function local_lionai_reports_getreport(int $id = 0): stdClass {
    global $DB;

    $list = [];
    $table = 'local_lionai_reports';
    $conditions = [];
    $conditions['id'] = $id;
    $record = $DB->get_record($table, $conditions);
    if (!$record) {
        throw new \moodle_exception('reportnotfound', 'local_lionai_reports');
    }

    $crexists = local_lionai_reports_check_for_cr();

    $record->link = (new moodle_url('/local/lionai_reports/', ['id' => $record->id]))->out();
    $record->creator = local_lionai_reports_ownername($record->userid);
    $record->actions = new stdClass();
    $record->actions->sesskey = sesskey();
    $record->actions->deleteactionurl = (new moodle_url('/local/lionai_reports/', ['id' => $record->id]))->out();
    $record->actions->exportcrautoactionurl = $crexists ? (new moodle_url(
        '/local/lionai_reports/',
        ['id' => $record->id, 'export' => 'confreports']
    ))->out() : '';
    $record->actions->exportcrxmlactionurl = (new moodle_url(
        '/local/lionai_reports/',
        ['id' => $record->id, 'export' => 'confreports']
    ))->out();
    $record->actions->exportsqlactionurl = (new moodle_url(
        '/local/lionai_reports/',
        ['id' => $record->id, 'export' => 'sqlformat']
    ))->out();
    $record->actions->exportcsvactionurl = (new moodle_url(
        '/local/lionai_reports/export.php',
        ['id' => $record->id]
    ))->out();

    $record->actions->is_new = empty($record->options) ? 'd-none' : 'd-flex';

    // Decode the options a single time.
    $options = json_decode((string) $record->options);
    $history = $options->history ?? [];

    $record->lastmessages = local_lionai_reports_getlast_messages(
        $history ? array_map(static fn($item) => (array) $item, $history) : null
    );

    foreach ($history as $key => $value) {
        if ($value->role == 'assistant') {
            $history[$key]->classes = $value->role . ' ms-4';
        } else {
            $history[$key]->classes = $value->role;
        }
    }

    $record->history = $history;

    return $record;
}

/**
 * Updates a LionAI report.
 *
 * @param int    $id     The ID of the report to update.
 * @param string $action The action to perform (update, delete, etc.).
 * @param string|null $data The data to update the report with.
 *
 * @return bool True if the update was successful; otherwise, false.
 */
function local_lionai_reports_updatereport(int $id = 0, string $action = 'update', ?string $data = null): bool {
    global $DB;
    $updateresult = false;

    switch ($action) {
        case 'update':
            $table = 'local_lionai_reports';
            $conditions = [];
            $conditions['id'] = $id;
            $record = $DB->get_record($table, $conditions);
            if (!$record) {
                break;
            }
            $data = json_decode($data);
            // Only allow fields that are safe to update from a client request.
            $allowed = ['name'];
            foreach ((array) $data as $key => $value) {
                if (in_array($key, $allowed, true) && is_scalar($value)) {
                    $record->{$key} = clean_param($value, PARAM_TEXT);
                }
            }
            $updateresult = $DB->update_record($table, $record);
            break;
        default:
            break;
    }

    return $updateresult;
}

/**
 * Adds a new LionAI report.
 *
 * @return int The ID of the newly added report.
 */
function local_lionai_reports_addreport(): int {
    global $DB, $USER;

    $table = 'local_lionai_reports';

    $currenttimestamp = time(); // Store the current timestamp.

    $newreport = new stdClass();
    $newreport->name = "Report " . date("H:i d.m.y", $currenttimestamp); // Default name based on the current time.
    $newreport->userid = $USER->id; // Current user's ID.
    $newreport->options = null; // Default options is (null).
    $newreport->timecreated = $currenttimestamp; // Use the same timestamp for timecreated.
    $newreport->timemodified = $currenttimestamp; // Use the same timestamp for timemodified.

    $resultid = $DB->insert_record($table, $newreport);

    return $resultid;
}

/**
 * Deletes a LionAI report.
 *
 * @param int $id The ID of the report to delete.
 *
 * @return bool True if the report was deleted successfully; otherwise, false.
 */
function local_lionai_reports_deletereport(int $id): bool {
    global $DB;

    $table = 'local_lionai_reports';

    if (!$report = $DB->get_record($table, ['id' => $id])) {
        return false; // Report not found.
    }

    $DB->delete_records($table, ['id' => $id]);

    return true; // Report deleted successfully.
}

/**
 * Executes a SQL query and retrieves the results.
 *
 * @param string $query The SQL query to execute.
 * @param bool $limitoff whether to use the limit for preview or not, for export.
 *
 * @return array An array containing the execution status, an optional error message, and the query results.
 */
function local_lionai_reports_getresult(string $query = '', bool $limitoff = false): array {
    global $DB;

    $records = [];
    $status = 0;
    $message = '';
    $count = 0;

    // Remove trailing semicolons and the LIMIT clause.
    $query = rtrim((string) $query, "; \t\r\n");
    $preparedquery = local_lionai_reports_removelimitclause($query);
    $trimmed = $preparedquery != $query;

    // Only a single, read-only SELECT (or WITH ... SELECT) statement is allowed.
    if (!local_lionai_reports_is_select_only($preparedquery)) {
        return [$status, get_string('only_select', 'local_lionai_reports'), $records, 0];
    }

    // Get the limit of records from configuration, and set a max of 500.
    $limitrecords = get_config('local_lionai_reports', 'lionai_reports_limitrecords');
    $limitrecords = $limitrecords > 500 ? 500 : $limitrecords;
    $limitrecords = $limitoff ? 0 : $limitrecords;

    $restore = local_lionai_reports_readonly_session();
    try {
        // Fetch the page of records once.
        $records = [];
        $rs = $DB->get_recordset_sql($preparedquery, null, 0, $limitrecords);
        foreach ($rs as $record) {
            $records[] = $record;
        }
        $rs->close();

        // The total number of rows: no extra query when everything was fetched or the page is not full.
        $count = count($records);
        if ($limitrecords > 0 && $count >= $limitrecords) {
            try {
                $count = (int) $DB->count_records_sql("SELECT COUNT(1) FROM ($preparedquery) lionai_count");
            } catch (Exception $e) {
                // The query cannot be wrapped (e.g. duplicate column names): count by streaming the rows.
                $count = 0;
                $countrs = $DB->get_recordset_sql($preparedquery);
                foreach ($countrs as $unused) {
                    $count++;
                }
                $countrs->close();
            }
        }
        $status = 2;
        $message .= $trimmed ? get_string('limited_to', 'local_lionai_reports', $limitrecords) : '';
    } catch (Exception $e) {
        // Log the real error (it can contain SQL fragments / schema details) and return a generic message.
        $debuginfo = $e instanceof moodle_exception || $e instanceof dml_exception ? ' ' . $e->debuginfo : '';
        debugging('local_lionai_reports: report query failed: ' . $e->getMessage() . $debuginfo, DEBUG_DEVELOPER);
        $message = $e instanceof dml_exception ? $e->getMessage() : get_string('queryerror', 'local_lionai_reports');
    } finally {
        $restore();
    }

    return [$status, $message, $records, $count];
}

/**
 * Streams the result of a report query to the browser as a CSV file, row by row from a single recordset.
 *
 * The number of exported rows is capped by the "lionai_reports_exportlimit" setting, and no count pass is made.
 *
 * @param string $query The SQL query.
 * @param string $filename The file name (without extension).
 *
 * @throws moodle_exception When the query is not allowed or returns no data (before any output is sent).
 */
function local_lionai_reports_export_csv(string $query, string $filename): void {
    global $DB;

    $query = local_lionai_reports_removelimitclause(rtrim((string) $query, "; \t\r\n"));
    if (!local_lionai_reports_is_select_only($query)) {
        throw new \moodle_exception('only_select', 'local_lionai_reports');
    }

    $cap = (int) get_config('local_lionai_reports', 'lionai_reports_exportlimit');
    $cap = $cap > 0 ? $cap : 100000;

    $restore = local_lionai_reports_readonly_session(600);
    $rs = null;
    try {
        $rs = $DB->get_recordset_sql($query, null, 0, $cap);
        if (!$rs->valid()) {
            throw new \moodle_exception('no_data_found', 'local_lionai_reports');
        }
        $columns = array_keys((array) $rs->current());

        // Rows are yielded one by one, so memory use does not depend on the size of the result.
        $rows = (function () use ($rs) {
            foreach ($rs as $record) {
                yield (array) $record;
            }
        })();

        \core\dataformat::download_data($filename, 'csv', $columns, $rows);
    } finally {
        if ($rs) {
            $rs->close();
        }
        $restore();
    }
}

/**
 * Replaces comments, string literals and quoted identifiers of a SQL statement with placeholders, so the remaining
 * "skeleton" can be inspected for keywords without being fooled by (or over-blocking on) quoted text.
 *
 * @param string $sql The SQL statement.
 * @param string $vendor The DB vendor (mysql, postgres, ...).
 *
 * @return string|null The skeleton, or null when the statement cannot be analysed safely (and must be rejected).
 */
function local_lionai_reports_sql_skeleton(string $sql, string $vendor): ?string {
    $mysql = ($vendor === 'mysql');
    $postgres = ($vendor === 'postgres');
    $backtick = chr(96);
    $len = strlen($sql);
    $out = '';
    $i = 0;

    while ($i < $len) {
        $c = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';

        // String literals and quoted identifiers.
        if ($c === "'" || $c === '"' || $c === $backtick) {
            // Postgres escape strings (E'...') and unicode escapes can hide characters: reject.
            if (
                $postgres && $c === "'" && $i > 0 && in_array($sql[$i - 1], ['e', 'E'], true)
                    && ($i < 2 || !preg_match('/[A-Za-z0-9_]/', $sql[$i - 2]))
            ) {
                return null;
            }
            $backslash = $mysql && $c !== $backtick;
            $j = $i + 1;
            $closed = false;
            while ($j < $len) {
                if ($backslash && $sql[$j] === '\\') {
                    $j += 2;
                    continue;
                }
                if ($sql[$j] === $c) {
                    if ($j + 1 < $len && $sql[$j + 1] === $c) {
                        $j += 2; // Doubled quote.
                        continue;
                    }
                    $closed = true;
                    break;
                }
                $j++;
            }
            if (!$closed) {
                return null;
            }
            $out .= $c === $backtick ? ' _identifier_ ' : ' _literal_ ';
            $i = $j + 1;
            continue;
        }

        // Block comments (MySQL executable comments /*! ... */ are rejected as they run as SQL).
        if ($c === '/' && $next === '*') {
            if ($mysql && $i + 2 < $len && ($sql[$i + 2] === '!' || substr($sql, $i + 2, 2) === 'M!')) {
                return null;
            }
            $depth = 1;
            $j = $i + 2;
            while ($j < $len && $depth > 0) {
                if ($sql[$j] === '*' && $j + 1 < $len && $sql[$j + 1] === '/') {
                    $depth--;
                    $j += 2;
                } else if ($postgres && $sql[$j] === '/' && $j + 1 < $len && $sql[$j + 1] === '*') {
                    $depth++;
                    $j += 2;
                } else {
                    $j++;
                }
            }
            if ($depth > 0) {
                return null;
            }
            $out .= ' ';
            $i = $j;
            continue;
        }

        // Line comments: "--" (MySQL needs a following whitespace) and "#" (MySQL only).
        if (
            ($c === '-' && $next === '-' && (!$mysql || $i + 2 >= $len || ctype_space($sql[$i + 2]) || ord($sql[$i + 2]) < 32))
                || ($mysql && $c === '#')
        ) {
            $j = $i;
            while ($j < $len && $sql[$j] !== "\n" && $sql[$j] !== "\r") {
                $j++;
            }
            $out .= ' ';
            $i = $j;
            continue;
        }

        $out .= $c;
        $i++;
    }

    // Postgres dollar-quoted strings can hide arbitrary text.
    if ($postgres && preg_match('/\$[A-Za-z_]*\$/', $out)) {
        return null;
    }

    return $out;
}

/**
 * Checks that the SQL is a single read-only SELECT statement.
 *
 * This is an allowlist (must start with SELECT or WITH) applied to the statement skeleton (see
 * local_lionai_reports_sql_skeleton), plus a list of constructs that are dangerous inside a SELECT
 * (data modifying CTEs, SELECT INTO / OUTFILE, row locks, and functions with side effects or file access).
 * It is a first line of defence: queries also run in a read-only DB session, see local_lionai_reports_readonly_session().
 *
 * @param string $sql The SQL query to check.
 *
 * @return bool True if the query is allowed to be executed.
 */
function local_lionai_reports_is_select_only(string $sql): bool {
    global $DB;

    $skeleton = local_lionai_reports_sql_skeleton((string) $sql, $DB->get_dbfamily());
    if ($skeleton === null) {
        return false;
    }

    $skeleton = strtolower(trim($skeleton));
    $skeleton = rtrim($skeleton, "; \t\r\n");

    // A single statement only.
    if (strpos($skeleton, ';') !== false) {
        return false;
    }

    // Must be a SELECT, or a WITH (CTE) query.
    if (!preg_match('/^[\s(]*(select|with)\b/', $skeleton)) {
        return false;
    }

    // Constructs that write data, export data or take locks (also blocks data modifying CTEs in Postgres).
    $keywords = '/\b(insert|update|delete|merge|truncate|drop|alter|create|into|outfile|dumpfile|lock|grant|revoke)\b/';
    if (preg_match($keywords, $skeleton)) {
        return false;
    }

    // Functions with side effects, file/network access or that can be used for denial of service.
    $functions = '/\b(sleep|benchmark|load_file|get_lock|release_lock|release_all_locks|is_free_lock|is_used_lock|'
        . 'master_pos_wait|sys_\w+|xp_\w+|pg_\w+|lo_\w+|dblink\w*|set_config|nextval|setval|'
        . 'query_to_xml\w*|database_to_xml\w*|table_to_xml\w*|cursor_to_xml)\s*\(/';
    if (preg_match($functions, $skeleton)) {
        return false;
    }

    return true;
}

/**
 * Switches the current DB session to read-only with a statement timeout (defence in depth), when supported.
 *
 * @param int $timeout Statement timeout in seconds.
 *
 * @return callable A callable that restores the session settings.
 */
function local_lionai_reports_readonly_session(int $timeout = 60): \Closure {
    global $DB;

    $vendor = $DB->get_dbfamily();
    $on = [];
    $off = [];

    if ($vendor === 'mysql') {
        $on[] = 'SET SESSION TRANSACTION READ ONLY';
        $off[] = 'SET SESSION TRANSACTION READ WRITE';
        if ($DB->get_dbvendor() === 'mariadb') {
            $on[] = 'SET SESSION max_statement_time = ' . (int) $timeout;
            $off[] = 'SET SESSION max_statement_time = 0';
        } else {
            $on[] = 'SET SESSION max_execution_time = ' . ((int) $timeout * 1000);
            $off[] = 'SET SESSION max_execution_time = 0';
        }
    } else if ($vendor === 'postgres') {
        $on[] = 'SET SESSION CHARACTERISTICS AS TRANSACTION READ ONLY';
        $on[] = 'SET statement_timeout = ' . ((int) $timeout * 1000);
        $off[] = 'SET SESSION CHARACTERISTICS AS TRANSACTION READ WRITE';
        $off[] = 'SET statement_timeout = DEFAULT';
    }

    $applied = [];
    foreach ($on as $index => $statement) {
        try {
            $DB->execute($statement);
            $applied[] = $off[$index];
        } catch (Exception $e) {
            debugging('LionAI Reports: could not apply "' . $statement . '": ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    return function () use ($DB, $applied) {
        foreach (array_reverse($applied) as $statement) {
            try {
                $DB->execute($statement);
            } catch (Exception $e) {
                debugging('LionAI Reports: could not restore DB session: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    };
}

/**
 * Remove the LIMIT clause from an SQL query.
 *
 * @param string $query The SQL query to process.
 *
 * @return string The SQL query without the LIMIT clause.
 */
function local_lionai_reports_removelimitclause(string $query): string {
    $pattern = '/\s+LIMIT\s+\d+(?:\s*,\s*\d+)?\s*$/i';
    return preg_replace($pattern, '', $query);
}

/**
 * Appends a history item to a report.
 *
 * @param int    $reportid     The ID of the report to which the history item is added.
 * @param stdClass $historyitem The history item to add.
 *
 * @return bool True if the history was updated.
 */
function local_lionai_reports_put_history(int $reportid, stdClass $historyitem): bool {
    global $DB;

    // Only the raw record is needed here (not the presentation data built by local_lionai_reports_getreport).
    $report = $DB->get_record('local_lionai_reports', ['id' => $reportid], '*', MUST_EXIST);

    $options = $report->options ? json_decode($report->options) : new stdClass();
    if (!is_object($options)) {
        $options = new stdClass();
    }
    $history = $options->history ?? [];

    $lasthistoryitem = end($history);
    if ($lasthistoryitem && $lasthistoryitem->role === $historyitem->role && $lasthistoryitem->content === $historyitem->content) {
        return false;
    }

    $history[] = $historyitem;
    $options->history = $history;

    $update = new stdClass();
    $update->id = $report->id;
    $update->options = json_encode($options);
    $update->timemodified = time();

    return $DB->update_record('local_lionai_reports', $update);
}

/**
 * Get the last messages for user and assistant roles from history data.
 *
 * @param array|null $historydata The history data array.
 * @return array An associative array containing 'lastusercontent' and 'lastassistantcontent'.
 */
function local_lionai_reports_getlast_messages(?array $historydata): array {
    // Initialize variables to store the last items for each role.
    $lastusercontent = null;
    $lastassistantcontent = null;

    if ($historydata) {
        // Loop through the history items in reverse order.
        foreach (array_reverse($historydata) as $item) {
            if ($item['role'] === 'user' && $lastusercontent === null) {
                $lastusercontent = $item['content'];
            }
            if ($item['role'] === 'assistant' && $lastassistantcontent === null) {
                $lastassistantcontent = $item['content'];
            }
            // If both last items are found, break out of the loop.
            if ($lastusercontent !== null && $lastassistantcontent !== null) {
                break;
            }
        }
    }
    // Return the last messages in an associative array.
    return [
        'lastusercontent' => $lastusercontent,
        'lastassistantcontent' => $lastassistantcontent,
    ];
}

/**
 * Exports a configurable report as an XML file or auto-imports it into a course.
 *
 * @param int  $id         The ID of the report to export or auto-import.
 * @param bool $autoimport True if auto-import is enabled; otherwise, false.
 *
 * @return void
 */
function local_lionai_reports_export_confreports($id, $autoimport = false) {
    global $CFG, $PAGE, $DB;

    $report = local_lionai_reports_getreport($id);

    $context = context_system::instance();
    $PAGE->set_context($context);

    $report->lastassistantcontent = $report->lastmessages['lastassistantcontent'] ?? '';
    $downloadfilename = clean_filename(format_string($report->name)) . '.xml';

    $data = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    $data .= "<report version=\"2020110300\">";

    $reportdata = [
        'visible' => '1',
        'summaryformat' => '1',
        'type' => 'sql',
        'pagination' => '0',
        'jsordering' => '1',
        'global' => '0',
        'lastexecutiontime' => '1',
        'cron' => '0',
        'remote' => '0',
        'export' => 'csv,xls,',
    ];

    $reportdata['name'] = "[lionai_reports] promt: " . $report->name;
    $reportdata['summary'] = $report->name;

    $components = [
        'customsql' => [
            'config' => (object) [
                'querysql' => $report->lastassistantcontent,
                'courseid' => 1,
                'submitbutton' => 'Save changes',
                'reportcategories' => '0',
                'reportsincategory' => '0',
                'remotequerysql' => '',
            ],
        ],
    ];

    $components = local_lionai_reports_cr_serialize($components);

    $reportdata['components'] = base64_encode($components);

    foreach ($reportdata as $key => $value) {
        $data .= "<$key><![CDATA[$value]]></$key>\n";
    }

    $data .= "</report>";

    if (!$autoimport) {
        if (strpos($CFG->wwwroot, 'https://') === 0) {
            @header('Cache-Control: max-age=10');
            @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
            @header('Pragma: ');
        } else {
            @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
            @header('Pragma: no-cache');
        }
        header("Content-type: text/xml; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$downloadfilename\"");

        print($data);
    }

    $courseid = 1;
    $course = $DB->get_record("course", ['id' => $courseid]);

    // If autoimport && function cr_import_xml is present.
    if ($autoimport) {
        if (local_lionai_reports_check_for_cr()) {
            $reportid = local_lionai_reports_cr_import_xml($data, $course);
            if ($reportid) {
                redirect(
                    "$CFG->wwwroot/blocks/configurable_reports/viewreport.php?id={$reportid}",
                    get_string('reportcreated', 'block_configurable_reports')
                );
            } else {
                print('errorimporting');
            }
        }
    }
}

/**
 * Checks if the CR (Configurable Reports) plugin is available.
 *
 * @return bool True if CR is available, false otherwise.
 */
function local_lionai_reports_check_for_cr(): bool {
    global $CFG;

    if (!file_exists($CFG->dirroot . '/blocks/configurable_reports/locallib.php')) {
        return false;
    }

    require_once($CFG->dirroot . '/blocks/configurable_reports/locallib.php');

    if (!function_exists('cr_import_xml')) {
        return false;
    }

    return true;
}

/**
 * Serializes a variable with support for URL encoding.
 *
 * @param mixed $var The variable to serialize.
 *
 * @return string The serialized and URL-encoded variable.
 */
function local_lionai_reports_cr_serialize($var) {
    return serialize(local_lionai_reports_urlencode_recursive($var));
}

/**
 * URL-encodes a variable.
 *
 * @param mixed $var The variable to encode.
 *
 * @return mixed The encoded variable.
 */
function local_lionai_reports_urlencode_recursive($var) {
    if (is_object($var)) {
        $newvar = new \stdClass();
        $properties = get_object_vars($var);
        foreach ($properties as $property => $value) {
            $newvar->$property = local_lionai_reports_urlencode_recursive($value);
        }
    } else if (is_array($var)) {
        $newvar = [];
        foreach ($var as $property => $value) {
            $newvar[$property] = local_lionai_reports_urlencode_recursive($value);
        }
    } else if (is_string($var)) {
        $newvar = urlencode($var);
    } else {
        // Nulls, integers, etc.
        $newvar = $var;
    }

    return $newvar;
}

/**
 * Exports the report sql content as an sql file.
 *
 * @param int  $id         The ID of the report to export or auto-import.
 *
 * @return void
 */
function local_lionai_reports_export_sqlformat($id) {
    global $CFG, $PAGE, $DB;

    $report = local_lionai_reports_getreport($id);

    $context = context_system::instance();
    $PAGE->set_context($context);

    $report->lastassistantcontent = $report->lastmessages['lastassistantcontent'] ?? '';
    $downloadfilename = clean_filename(format_string($report->name)) . '.sql';

    $data = $report->lastassistantcontent;

    if (strpos($CFG->wwwroot, 'https://') === 0) {
        @header('Cache-Control: max-age=10');
        @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        @header('Pragma: ');
    } else {
        @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        @header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        @header('Pragma: no-cache');
    }
    header("Content-type: text/xml; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$downloadfilename\"");

    print($data);
}

/**
 * Exports the report to configurable reports plugin.
 *
 * @param string $xml xml formate of the report.
 * @param int $course course id.
 * @return void
 */
function local_lionai_reports_cr_import_xml($xml, $course) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot . '/lib/xmlize.php');
    $data = xmlize($xml, 1, 'UTF-8');

    if (isset($data['report']['@']['version'])) {
        $newreport = new stdclass();
        foreach ($data['report']['#'] as $key => $val) {
            if ($key == 'components') {
                $val[0]['#'] = base64_decode(trim($val[0]['#']));
                // Fix url_encode " and ' when importing SQL queries.
                $tempcomponents = cr_unserialize($val[0]['#']);
                if (array_key_exists('customsql', $tempcomponents)) {
                    $tempcomponents['customsql']['config']->querysql =
                            str_replace("\'", "'", $tempcomponents['customsql']['config']->querysql);
                    $tempcomponents['customsql']['config']->querysql =
                            str_replace('\"', '"', $tempcomponents['customsql']['config']->querysql);
                }
                $val[0]['#'] = cr_serialize($tempcomponents);
            }
            $newreport->{$key} = trim($val[0]['#']);
        }
        $newreport->courseid = $course->id;
        $newreport->ownerid = $USER->id;
        $newreport->name .= " (" . userdate(time()) . ")";

        $id = $DB->insert_record('block_configurable_reports', $newreport);

        if (!$id) {
            return false;
        }
        return $id;
    }
    return false;
}
