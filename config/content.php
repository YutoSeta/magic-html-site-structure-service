<?php

return [
    'service_token' => env('MAGIC_HTML_SERVICE_TOKEN'),
    'writes_per_minute' => (int) env('CONTENT_WRITES_PER_MINUTE', 60),
    'public_reads_per_minute' => (int) env('CONTENT_PUBLIC_READS_PER_MINUTE', 120),
];
