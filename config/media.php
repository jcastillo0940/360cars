<?php

return [
    'vehicle_disk' => env('VEHICLE_MEDIA_DISK', 'public'),
    'staging_disk' => env('VEHICLE_MEDIA_STAGING_DISK', 'local'),
    'queue_connection' => env('MEDIA_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
    'queue_name' => env('MEDIA_QUEUE_NAME', 'media'),
    'main_max_width' => (int) env('VEHICLE_IMAGE_MAX_WIDTH', 1920),
    'thumb_max_width' => (int) env('VEHICLE_IMAGE_THUMB_WIDTH', 480),
    'webp_quality' => (int) env('VEHICLE_IMAGE_WEBP_QUALITY', 82),
];
