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
 * Services definition for local_notification_manager.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_notification_manager_search_users' => [
        'classname'   => 'local_notification_manager\external\search_users',
        'methodname'  => 'execute',
        'description' => 'Searches for users.',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'local_notification_manager_delete_notifications' => [
        'classname'   => 'local_notification_manager\external\delete_notifications',
        'methodname'  => 'execute',
        'description' => 'Deletes notifications.',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_notification_manager_trash_action' => [
        'classname'   => 'local_notification_manager\external\trash_action',
        'methodname'  => 'execute',
        'description' => 'Performs actions on trashed notifications.',
        'type'        => 'write',
        'ajax'        => true,
    ],
];
