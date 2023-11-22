<?php

return [
    // JWT Settings
    'jwt_secret' => env('JWT_SECRET', env('APP_KEY')),
    'jwt_access' => env('JWT_ACCESS', 900),
    'jwt_refresh' => env('JWT_REFRESH', 7200),
];