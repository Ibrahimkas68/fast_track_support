<?php

return [
    'driver' => env('URGENCY_DRIVER', 'local'),
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'connect_timeout' => env('URGENCY_CONNECT_TIMEOUT', 2),
    'timeout' => env('URGENCY_TIMEOUT', 5),
    'rate_limit' => env('TICKET_RATE_LIMIT', 10),
];
