<?php

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/notification_manager:manage', $context);

$search = optional_param('q', '', PARAM_RAW_TRIMMED);
require_sesskey();
$items = [];

if ($search !== '') {
    $query = '%' . core_text::strtolower($search) . '%';
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

header('Content-Type: application/json');
echo json_encode(['items' => $items]);
