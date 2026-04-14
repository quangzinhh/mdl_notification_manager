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
 * External web service API for deleting notifications.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notification_manager\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

defined('MOODLE_INTERNAL') || die();

class delete_notifications extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID', VALUE_REQUIRED),
            'ids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Notification ID'),
                'List of notification IDs'
            ),
            'action' => new external_value(PARAM_ALPHA, 'Action: soft or hard', VALUE_DEFAULT, 'hard'),
        ]);
    }

    /**
     * Execute the delete
     *
     * @param int $userid
     * @param array $ids
     * @param string $action
     * @return array
     */
    public static function execute($userid, $ids, $action) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
            'ids' => $ids,
            'action' => $action,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/notification_manager:manage', $context);

        $userid = $params['userid'];
        $notificationids = $params['ids'];
        $action = $params['action'];

        if ($userid <= 0) {
            return [
                'success' => false,
                'message' => get_string('invalid_user_id', 'local_notification_manager'),
            ];
        }

        if (empty($notificationids)) {
            return [
                'success' => false,
                'message' => get_string('no_notifications_selected', 'local_notification_manager'),
            ];
        }

        list($inorsql, $sqlparams) = $DB->get_in_or_equal($notificationids, SQL_PARAMS_NAMED, 'notif');
        $sqlparams['useridto'] = $userid;
        
        $sql = "useridto = :useridto AND id $inorsql";
        $count = $DB->count_records_select('notifications', $sql, $sqlparams);

        if ($count > 0) {
            try {
                if ($action === 'soft') {
                    $rs = $DB->get_recordset_select('notifications', $sql, $sqlparams);
                    foreach ($rs as $rec) {
                        $trash = new \stdClass();
                        $trash->originalid = $rec->id;
                        $trash->useridto = $rec->useridto;
                        $trash->subject = $rec->subject;
                        $trash->component = $rec->component;
                        $trash->timecreated = $rec->timecreated;
                        $trash->timeread = $rec->timeread;
                        $trash->timedeleted = time();
                        $trash->rawdata = json_encode($rec);
                        
                        $DB->insert_record('local_notification_manager_trash', $trash);
                    }
                    $rs->close();
                }
                
                $DB->delete_records_select('notifications', $sql, $sqlparams);
                
                $msgkey = ($action === 'soft') ? 'success_trashed' : 'success_deleted';
                return [
                    'success' => true,
                    'count' => $count,
                    'message' => get_string($msgkey, 'local_notification_manager', $count),
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => get_string('error_delete', 'local_notification_manager') . ' ' . $e->getMessage(),
                ];
            }
        } 

        return [
            'success' => false,
            'message' => get_string('no_matching_notifications', 'local_notification_manager'),
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Status of the operation'),
            'message' => new external_value(PARAM_TEXT, 'Response message'),
            'count' => new external_value(PARAM_INT, 'Number of notifications deleted', VALUE_OPTIONAL),
        ]);
    }
}
