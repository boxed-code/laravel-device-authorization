<?php

return [

    'lifetimes' => [

        'authorization' => env('DEVICE_AUTH_LIFETIME', 60 * 60 * 24 * 365),

        'request' => env('DEVICE_AUTH_REQUEST_LIFETIME', 60 * 60 * 1),

    ],

    'default_fingerprinter' => 'request',

    'fingerprinters' => [

        'request' => [
            'provider' => 'request',
            'keys' => [
                'server' => [
                    'HTTP_USER_AGENT',
                    'HTTP_ACCEPT_LANGUAGE',
                    'HTTP_ACCEPT',
                    'HTTP_ACCEPT_CHARSET',
                ]
            ]
        ]

    ],

    'authorization_model' => \BoxedCode\Laravel\Auth\Device\Models\DeviceAuthorization::class,

    'notification' => \BoxedCode\Laravel\Auth\Device\Notifications\AuthorizationRequest::class,
];