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
 * English strings for local_notification_manager.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Notification Manager';
$string['dashboardtitle'] = 'Notification Manager';
$string['searchusers'] = 'Search for a user...';
$string['pleaseselectuser'] = 'Please select or search for a user to view their notifications.';
$string['notificationsfor'] = 'Notifications for {$a}';
$string['search'] = 'Search';
$string['filter_all'] = 'All';
$string['filter_read'] = 'Read';
$string['filter_unread'] = 'Unread';
$string['col_subject'] = 'Subject';
$string['col_message'] = 'Message';
$string['col_component'] = 'Component';
$string['col_timecreated'] = 'Time Created';
$string['col_timeread'] = 'Time Read';
$string['delete_selected'] = 'Delete Selected';
$string['confirm_delete'] = 'Are you sure you want to delete the selected notifications? This action cannot be undone.';
$string['no_notifications'] = 'No notifications found for this user.';
$string['error_permission'] = 'You do not have permission to manage notifications.';
$string['success_deleted'] = 'Successfully deleted {$a} notification(s).';
$string['error_delete'] = 'An error occurred while deleting notifications.';
$string['notification_manager:manage'] = 'Manage notifications';
$string['select_all'] = 'Select All';
$string['tab_dashboard'] = 'Dashboard';
$string['tab_manage'] = 'Manage Notifications';
$string['time_range'] = 'Time Range';
$string['time_7_days'] = 'Last 7 Days';
$string['time_30_days'] = 'Last 30 Days';
$string['time_90_days'] = 'Last 90 Days';
$string['time_all'] = 'All Time';
$string['analytic_engagement'] = 'Engagement';
$string['analytic_unread_rate'] = 'Unread Rate';
$string['analytic_total'] = 'Total Notifications';
$string['analytic_read'] = 'Read';
$string['analytic_unread'] = 'Unread';
$string['analytic_top_users'] = 'Top Users';
$string['analytic_popular_types'] = 'Popular Types';
$string['tab_trash'] = 'Trash';
$string['move_to_trash'] = 'Move to Trash';
$string['delete_permanently'] = 'Permanently Delete';
$string['delete_selected_permanently'] = 'Delete Selected (Permanently)';
$string['restore_selected'] = 'Restore Selected';
$string['col_timedeleted'] = 'Time Deleted';
$string['no_trash_notifications'] = 'No notifications found in trash.';
$string['success_trashed'] = 'Successfully moved {$a} notification(s) to trash.';
$string['success_restored'] = 'Successfully restored {$a} notification(s).';
$string['confirm_move_trash'] = 'Are you sure you want to move the selected notifications to the trash?';
$string['confirm_delete_permanently'] = 'Are you sure you want to PERMANENTLY delete the selected notifications? This action cannot be undone.';
$string['confirm_restore'] = 'Are you sure you want to restore the selected notifications?';
$string['cleanup_trash_task'] = 'Cleanup old notifications from trash';
$string['invalid_user_id'] = 'Invalid user ID.';
$string['no_notifications_selected'] = 'No notifications selected.';
$string['unknown_action'] = 'Unknown action.';
$string['no_matching_notifications'] = 'No matching notifications found for this user.';

// Privacy strings
$string['privacy:metadata:trash'] = 'The notification manager trash stores details about soft-deleted notifications for restoration purposes.';
$string['privacy:metadata:trash:useridto'] = 'The ID of the user that the notification was sent to.';
$string['privacy:metadata:trash:subject'] = 'The subject line of the notification.';
$string['privacy:metadata:trash:component'] = 'The Moodle component that generated the notification.';
$string['privacy:metadata:trash:timecreated'] = 'The timestamp when the notification was originally created.';
$string['privacy:metadata:trash:timedeleted'] = 'The timestamp when the notification was soft-deleted to the trash.';
