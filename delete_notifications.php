<?php

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/notification_manager:manage', $context);

$userid = optional_param('userid', 0, PARAM_INT);
$notificationids = optional_param_array('ids', [], PARAM_INT);

require_sesskey();

$response = ['success' => false, 'message' => ''];

if ($userid <= 0) {
    $response['message'] = 'Invalid user ID.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (empty($notificationids)) {
    $response['message'] = 'No notifications selected.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Ensure the notifications actually belong to the user
list($inorsql, $params) = $DB->get_in_or_equal($notificationids, SQL_PARAMS_NAMED, 'notif');
$params['useridto'] = $userid;

$sql = "useridto = :useridto AND id $inorsql";
$count = $DB->count_records_select('notifications', $sql, $params);

if ($count > 0) {
    try {
        $DB->delete_records_select('notifications', $sql, $params);
        $response['success'] = true;
        $response['count'] = $count;
        $response['message'] = get_string('success_deleted', 'local_notification_manager', $count);
    } catch (Exception $e) {
        $response['message'] = get_string('error_delete', 'local_notification_manager') . ' ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No matching notifications found for this user.';
}

header('Content-Type: application/json');
echo json_encode($response);
