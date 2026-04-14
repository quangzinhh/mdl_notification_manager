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
 * Privacy Subsystem implementation for local_notification_manager.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notification_manager\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_notification_manager.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns meta data about this plugin.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_notification_manager_trash',
            [
                'useridto' => 'privacy:metadata:trash:useridto',
                'subject' => 'privacy:metadata:trash:subject',
                'component' => 'privacy:metadata:trash:component',
                'timecreated' => 'privacy:metadata:trash:timecreated',
                'timedeleted' => 'privacy:metadata:trash:timedeleted',
            ],
            'privacy:metadata:trash'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {local_notification_manager_trash} t ON t.useridto = c.instanceid AND c.contextlevel = :contextlevel
                 WHERE t.useridto = :userid";

        $params = [
            'contextlevel' => CONTEXT_USER,
            'userid'       => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist $userlist The userlist containing the list of users who have data in this context.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        $sql = "SELECT useridto
                  FROM {local_notification_manager_trash}
                 WHERE useridto = :userid";
        $params = ['userid' => $context->instanceid];

        $userlist->add_from_sql('useridto', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        [$contextsql, $contextparams] = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT t.*, c.id as contextid
                  FROM {local_notification_manager_trash} t
                  JOIN {context} c ON c.instanceid = t.useridto AND c.contextlevel = :contextlevel
                 WHERE t.useridto = :userid
                   AND c.id {$contextsql}";

        $params = array_merge(['userid' => $userid, 'contextlevel' => CONTEXT_USER], $contextparams);
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $record) {
            $context = \context::instance_by_id($record->contextid);
            $data = (object)[
                'subject' => $record->subject,
                'component' => $record->component,
                'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
                'timeread' => $record->timeread ? \core_privacy\local\request\transform::datetime($record->timeread) : null,
                'timedeleted' => \core_privacy\local\request\transform::datetime($record->timedeleted),
                'content' => $record->rawdata,
            ];

            // Subcontext folder path inside the zip.
            $subcontext = [
                get_string('tab_trash', 'local_notification_manager'),
                $record->id,
            ];

            writer::with_context($context)->export_data($subcontext, $data);
        }
        $rs->close();
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param   \context $context A user context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        $DB->delete_records('local_notification_manager_trash', ['useridto' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $userid) {
                $DB->delete_records('local_notification_manager_trash', ['useridto' => $userid]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        $instanceid = $context->instanceid;
        if (in_array($instanceid, $userids)) {
            $DB->delete_records('local_notification_manager_trash', ['useridto' => $instanceid]);
        }
    }
}
