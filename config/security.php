<?php

return [
    'blocked_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('SECURITY_BLOCKED_IPS', ''))))),
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('SECURITY_ALLOWED_IPS', ''))))),
    'blocked_user_agents' => array_values(array_filter(array_map('trim', explode(',', (string) env('SECURITY_BLOCKED_USER_AGENTS', 'sqlmap,curl/,nikto,nmap,masscan'))))),
];
