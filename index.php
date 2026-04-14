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
 * Main index page for notification manager.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/notification_manager:manage', $context);

$timerange = optional_param('timerange', '30', PARAM_ALPHANUM);

$PAGE->set_url(new moodle_url('/local/notification_manager/index.php', ['timerange' => $timerange]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('dashboardtitle', 'local_notification_manager'));
$PAGE->set_heading(get_string('pluginname', 'local_notification_manager'));

$renderer = $PAGE->get_renderer('local_notification_manager');

echo $renderer->header();
echo $OUTPUT->heading(get_string('dashboardtitle', 'local_notification_manager'));

$tabs = [
    new tabobject(
        'dashboard',
        new moodle_url('/local/notification_manager/index.php'),
        get_string('tab_dashboard', 'local_notification_manager')
    ),
    new tabobject(
        'manage',
        new moodle_url('/local/notification_manager/manage.php'),
        get_string('tab_manage', 'local_notification_manager')
    ),
    new tabobject(
        'trash',
        new moodle_url('/local/notification_manager/trash.php'),
        get_string('tab_trash', 'local_notification_manager')
    ),
];
echo $OUTPUT->tabtree($tabs, 'dashboard');

require_once(__DIR__ . '/classes/output/dashboard.php');
echo $renderer->render(new \local_notification_manager\output\dashboard($timerange));

echo $renderer->footer();
