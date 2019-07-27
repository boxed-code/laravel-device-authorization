<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authorization Model
    |--------------------------------------------------------------------------
    |
    | If you would like to override the default device authorization model, 
    | you can do so within the section below.
    |
    */

    'authorization_model' => \BoxedCode\Laravel\Auth\Device\Models\DeviceAuthorization::class,
   
    /*
    |--------------------------------------------------------------------------
    | Lifetimes
    |--------------------------------------------------------------------------
    |
    | You can configure the lifetimes of various aspects of the package below, 
    | note that all lifetimes are specified in seconds.
    |
    */

    'lifetimes' => [

        /*
         | Users can be periodically required to reauthorize their devices. By default, 
         | device authorization are set to expire once a year. You can set this value to 
         | false to prevent authorizations from expiring.
         */
        'authorization' => env('DEVICE_AUTH_LIFETIME', 60 * 60 * 24 * 365),

        /*
         | All authorization requests issued are only valid for a specific length 
         | of time, you can set this here, by default this is set to one hour.
         */
        'request' => env('DEVICE_AUTH_REQUEST_LIFETIME', 60 * 60),

    ],

    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    |
    | Here you can specify the notification used for device authorizations.
    |
    */

    'notification' => \BoxedCode\Laravel\Auth\Device\Notifications\AuthorizationRequest::class,

    /*
    |--------------------------------------------------------------------------
    | Fingerprints
    |--------------------------------------------------------------------------
    |
    | Device fingerprints are created by hashing data from request data. We have 
    | set some sensible defaults below that will work for most cases.
    |
    | You can create further entropy by using a client side fingerprinting library 
    | such as fingerprint.js and posting a '_fingerprint' field with your usual 
    | authentication data. You will also need to uncomment the 'client_fingerprint' 
    | entry below which will add the '_fingerprint' posted to the data collected 
    | server-side and create an even stronger fingerprint for the device.
    |
    */

    'fingerprints' => [

        'algorithm' => 'sha256',

        'keys' => [
            'server' => [
                'HTTP_USER_AGENT',
                'HTTP_ACCEPT_LANGUAGE',
                'HTTP_ACCEPT'
            ],
            'session' => [
                //'client_fingerprint'
            ]
        ] 

    ],

];