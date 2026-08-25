<?php

return [
    'service_token' => env('MAGIC_HTML_SERVICE_TOKEN'),
    'media_disk' => env('CMS_MEDIA_DISK', env('FILESYSTEM_DISK', 's3')),
    'writes_per_minute' => (int) env('CMS_WRITES_PER_MINUTE', 60),
    'uploads_per_minute' => (int) env('CMS_UPLOADS_PER_MINUTE', 20),
];
