<?php

return [
    'preferred_socket' => env('CLAMAV_PREFERRED_SOCKET', 'tcp_socket'),
    'unix_socket' => env('CLAMAV_UNIX_SOCKET', '/var/run/clamav/clamd.ctl'),
    'tcp_socket' => env('CLAMAV_TCP_SOCKET', 'tcp://127.0.0.1:3310'),
    'socket_connect_timeout' => env('CLAMAV_SOCKET_CONNECT_TIMEOUT', 5),
    'socket_read_timeout' => env('CLAMAV_SOCKET_READ_TIMEOUT', 30),
    'client_exceptions' => env('CLAMAV_CLIENT_EXCEPTIONS', false),
    'skip_validation' => env('CLAMAV_SKIP_VALIDATION', env('APP_ENV') !== 'production'),
];
