<?php

return [
    'database' => [
        'host' => '127.0.0.1',
        'name' => 'trackify',
        'username' => 'your_database_user',
        'password' => 'replace_with_your_local_password',
    ],
    'jwt_secret' => 'replace_with_a_long_random_secret',
    'external_api' => [
        'open_meteo_base_url' => 'https://api.open-meteo.com/v1/forecast',
    ],
];
