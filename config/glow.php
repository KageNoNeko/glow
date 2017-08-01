<?php

return [
    'auth' => [
        'guard' => [
            'token' => [
                'table' => 'access_tokens',
                'expire' => env('GLOW_ACCESS_TOKENS_EXPIRE', 60),
                'multiple' => false,
            ],
        ],
    ],
];