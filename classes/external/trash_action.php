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
 * External web service API for trash actions.
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

class trash_action extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID filter', VALUE_DEFAULT, 0),
            'ids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Trash item ID'),
                'List of trash IDs'
            ),
            'action' => new external_value(PARAM_ALPHA, 'Action: restore or hard', VALUE_REQUIRED),
        ]);
    }

    /**
     * Execute the action
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
        $trashids = $params['ids'];
        $action = $params['action'];

        if (empty($trashids)) {
            return [
                'success' => false,
                'message' => get_string('no_notifications_selected', 'local_notification_manager'),
            ];
        }

        list($inorsql, $sqlparams) = $DB->get_in_or_equal($trashids, SQL_PARAMS_NAMED, 'trash');
        
        $sql = "id $inorsql";
        if ($userid > 0) {
            $sql .= " AND useridto = :useridto";
            $sqlparams['useridto'] = $userid;
        }

        $count = $DB->count_records_select('local_notification_manager_trash', $sql, $sqlparams);

        if ($count > 0) {
            try {
                if ($action === 'restore') {
                    $rs = $DB->get_recordset_select('local_notification_manager_trash', $sql, $sqlparams);
                    foreach ($rs as $rec) {
                        $raw = (array)json_decode($rec->rawdata);
                        unset($raw['id']); 
                        $DB->insert_record('notifications', (object)$raw);
                    }
                    $rs->close();
                    $DB->delete_records_select('local_notification_manager_trash', $sql, $sqlparams);
                    
                    return [
                        'success' => true,
                        'count' => $count,
                        'message' => get_string('success_restored', 'local_notification_manager', $count),
                    ];
                } else if ($action === 'hard') {
                    $DB->delete_records_select('local_notification_manager_trash', $sql, $sqlparams);
                    
                    return [
                        'success' => true,
                        'count' => $count,
                        'message' => get_string('success_deleted', 'local_notification_manager', $count),
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => get_string('unknown_action', 'local_notification_manager'),
                    ];
                }
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
            'count' => new external_value(PARAM_INT, 'Number of notifications mutated', VALUE_OPTIONAL),
        ]);
    }
}
