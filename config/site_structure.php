<?php

return [
    'service_token' => env('MAGIC_HTML_SERVICE_TOKEN'),
    'writes_per_minute' => (int) env('SITE_STRUCTURE_WRITES_PER_MINUTE', 60),
    'reads_per_minute' => (int) env('SITE_STRUCTURE_READS_PER_MINUTE', 300),
];
