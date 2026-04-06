<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/notification_manager:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ],
    ],
];
