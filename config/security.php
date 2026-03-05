<?php

declare(strict_types=1);

return [
    'session_name' => env_value('SESSION_NAME', 'attendance_session'),
    'session_lifetime' => (int) env_value('SESSION_LIFETIME', '7200'),
    'max_upload_size_mb' => (int) env_value('MAX_UPLOAD_SIZE_MB', '10'),
    'signed_sheet_dir' => env_value('SIGNED_SHEET_DIR', 'storage/private/signed-sheets'),
    'approval_sla_hours' => (int) env_value('LECTURER_APPROVAL_SLA_HOURS', '24'),
    'allowed_upload_mime' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ],
    'allowed_upload_ext' => ['pdf', 'jpg', 'jpeg', 'png'],
];
