<?php

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/notification_manager:manage', $context);

$userid = optional_param('userid', 0, PARAM_INT);
$userlabel = optional_param('userlabel', '', PARAM_RAW_TRIMMED);
$page = max(0, optional_param('page', 0, PARAM_INT));
$search = optional_param('search', '', PARAM_RAW_TRIMMED);
$filter = optional_param('filter', 'all', PARAM_ALPHA);

if ($userlabel !== '') {
    if (preg_match('/^\s*(\d+)\s*-/', $userlabel, $matches)) {
        $userid = (int)$matches[1];
    }
}

$PAGE->set_url(new moodle_url('/local/notification_manager/index.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('dashboardtitle', 'local_notification_manager'));
$PAGE->set_heading(get_string('pluginname', 'local_notification_manager'));

$renderer = $PAGE->get_renderer('local_notification_manager');

echo $renderer->header();
echo $OUTPUT->heading(get_string('dashboardtitle', 'local_notification_manager'));

require_once(__DIR__ . '/classes/output/main.php');
echo $renderer->render(new \local_notification_manager\output\main($userid, $userlabel, $page, $search, $filter));

echo $renderer->footer();
