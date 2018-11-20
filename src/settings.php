<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'rrd' => [
            'path' => __DIR__ . '/../storage/poulailler.rrd' ,
        ],

        'db' => [
            'path' => __DIR__ . '/../storage/poulailler.db' ,
        ],

        'json' => [
            'path' => __DIR__ . '/../storage/poulailler.json' ,
        ],

        'auth_token' => isset($_ENV['POULAILLER_TOKEN']) ? $_ENV['POULAILLER_TOKEN'] : '',
    ],
];
