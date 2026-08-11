<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Data Retention Windows
    |--------------------------------------------------------------------------
    |
    | Defines the retention period (in days) for each data category.
    | The purge commands will physically delete data older than these windows.
    | Minimum values are enforced by the commands — setting below the minimum
    | will cause the command to refuse execution.
    |
    */

    'audit_logs' => [
        'days' => (int) env('RETENTION_AUDIT_DAYS', 400),
        'minimum_days' => 400,
        'schedule' => 'monthly',
    ],

    'contacts' => [
        'months' => (int) env('RETENTION_CONTACTS_MONTHS', 24),
        'minimum_months' => 24,
        'schedule' => 'weekly',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Verification
    |--------------------------------------------------------------------------
    |
    | Purge commands refuse to run if the last backup is older than this.
    | Set to 0 to disable the check (not recommended in production).
    |
    */

    'backup_max_age_hours' => (int) env('RETENTION_BACKUP_MAX_AGE_HOURS', 0),

];
