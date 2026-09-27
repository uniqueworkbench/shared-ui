<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hard Abend Alert Recipient
    |--------------------------------------------------------------------------
    |
    | Uncaught exceptions ("hard abends") are recorded to the error_logs table
    | and an alert email is sent here. Override per-app via ERROR_ALERT_EMAIL.
    | Set to null to disable email alerts (rows are still recorded).
    |
    */

    'error_alert_email' => env('ERROR_ALERT_EMAIL', 'admin@uniqueworkbench.com'),

];
