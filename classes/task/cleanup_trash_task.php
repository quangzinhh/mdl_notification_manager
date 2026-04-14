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
 * Task to clean up trash.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notification_manager\task;

use core\task\scheduled_task;

/**
 * Task to clean up trash.
 */
class cleanup_trash_task extends scheduled_task {
    /**
     * Get the name of the task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('cleanup_trash_task', 'local_notification_manager');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        mtrace("Starting Notification Manager trash cleanup task...");

        $days = 30;
        $cutoff = time() - ($days * DAYSECS);

        $sql = "timedeleted < :cutoff";
        $params = ['cutoff' => $cutoff];
        $count = $DB->count_records_select('local_notification_manager_trash', $sql, $params);
        if ($count > 0) {
            mtrace("Found {$count} notifications to permanently delete.");
            $DB->delete_records_select('local_notification_manager_trash', $sql, $params);
            mtrace("Deleted successfully.");
        } else {
            mtrace("No notifications to delete.");
        }

        mtrace("Cleanup task finished.");
    }
}
