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
 * External web service API for searching users.
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

/**
 * External api to search users.
 */
class search_users extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'q' => new external_value(PARAM_TEXT, 'Search query string', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Execute the search
     *
     * @param string $q
     * @return array
     */
    public static function execute($q) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['q' => $q]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/notification_manager:manage', $context);

        $items = [];
        $search = $params['q'];

        if ($search !== '') {
            $query = '%' . \core_text::strtolower($search) . '%';
            $sql = "SELECT id, firstname, lastname, email
                      FROM {user}
                     WHERE deleted = 0
                       AND (" . $DB->sql_like('LOWER(firstname)', ':search1', false)
                        . " OR " . $DB->sql_like('LOWER(lastname)', ':search2', false)
                        . " OR " . $DB->sql_like('LOWER(email)', ':search3', false) . ")
                  ORDER BY firstname ASC, lastname ASC, id ASC";
            $records = $DB->get_records_sql($sql, [
                'search1' => $query,
                'search2' => $query,
                'search3' => $query,
            ], 0, 20);

            foreach ($records as $user) {
                $fullname = trim(fullname((object)[
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                ]));
                $items[] = [
                    'id' => (int)$user->id,
                    'fullname' => $fullname,
                    'email' => (string)$user->email,
                ];
            }
        }

        return ['success' => true, 'items' => $items];
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Status of the operation'),
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'User ID'),
                    'fullname' => new external_value(PARAM_TEXT, 'User full name'),
                    'email' => new external_value(PARAM_TEXT, 'User email address'),
                ]),
                'List of matching users',
                VALUE_OPTIONAL
            ),
        ]);
    }
}
