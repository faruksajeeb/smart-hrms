<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Queue document analysis
    |--------------------------------------------------------------------------
    |
    | When false, analysis runs immediately in the web request (no queue worker
    | required). Set to true and run "php artisan queue:work" for background jobs.
    |
    */

    'queue' => env('DOCUMENT_ANALYZER_QUEUE', false),

    'default_provider' => env('DOCUMENT_ANALYZER_DEFAULT_PROVIDER', 'openai'),

    'max_document_chars' => (int) env('DOCUMENT_ANALYZER_MAX_CHARS', 120000),

];
