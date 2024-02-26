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
 * Privacy provider for plugin.
 *
 * @package    local_lionai_reports
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lionai_reports\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for plugin
 */
class provider implements
    // This portfolio plugin does not store any data itself.
    \core_privacy\local\metadata\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider {

    // This trait must be included to provide the relevant polyfill for the metadata provider.
    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns meta data about this plugin.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this plugin.
     */
    public static function _get_metadata(collection $collection) :collection {

        $collection->add_database_table('local_lionai_reports', [
                'userid' => 'privacy:metadata:local_lionai_reports:userid',
                'options' => 'privacy:metadata:local_lionai_reports:options',
        ], 'privacy:metadata:local_lionai_reports');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid($userid) {
        $contextlist = new contextlist();

        // Find the reports created by the userid.
        $sql = "SELECT ctx.id
                FROM {local_lionai_reports} llr
                JOIN {context} ctx
                  ON ctx.instanceid = llr.userid AND ctx.contextlevel = :contextlevel
                WHERE llr.userid = :userid";

        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_USER];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function _get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $params = [
                'contextid' => $context->id,
                'contextuser' => CONTEXT_USER,
        ];

        $sql = "SELECT llr.userid as ownerid
                  FROM {local_lionai_reports} llr
                  JOIN {context} ctx
                       ON ctx.instanceid = llr.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $reportsdata = [];
        $sql = "SELECT CONCAT(u.firstname, ' ', u.lastname) AS fullname ,llr.*
                  FROM {local_lionai_reports} llr
                  JOIN {user} u ON u.id = llr.userid
                 WHERE llr.userid = :userid";
        $params = ['userid' => $contextlist->get_user()->id];
        $results = $DB->get_records_sql($sql, $params);
        foreach ($results as $result) {
            $reportsdata[] = (object) [
                    'fullname' => format_string($result->fullname, true),
                    'userid' => $result->userid,
                    'reportname' => $result->name,
                    'histoty' => $result->options,
                    'timecreated' => transform::datetime($result->timecreated),
                    'timemodified' => transform::datetime($result->timemodified),
            ];
        }
        if (!empty($reportsdata)) {
            $data = (object) [
                    'reports' => $reportsdata,
            ];
            writer::with_context($contextlist->current())->export_data([
                    get_string('pluginname', 'local_lionai_reports')], $data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function _delete_data_for_all_users_in_context(\context $context) {
        if ($context instanceof \context_user) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        static::delete_data($contextlist->get_user()->id);
    }

    /**
     * Delete data related to a userid.
     *
     * @param  int $userid The user ID
     */
    protected static function delete_data($userid) {
        global $DB;

        // Reports are considered to be 'owned' by the institution, even if they were originally written by a specific
        // user. They are still exported in the list of a users data, but they are not removed.
        // The ownerid is instead anonymised.
        $params['userid'] = $userid;
        $DB->set_field_select('local_lionai_reports', 'userid', 0, "userid = :userid", $params);
    }
}
