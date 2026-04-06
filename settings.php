<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category(
        'local_notification_manager',
        get_string('pluginname', 'local_notification_manager')
    ));

    $ADMIN->add('local_notification_manager', new admin_externalpage(
        'local_notification_manager_dashboard',
        get_string('dashboardtitle', 'local_notification_manager'),
        new moodle_url('/local/notification_manager/index.php'),
        'local/notification_manager:manage'
    ));
}
