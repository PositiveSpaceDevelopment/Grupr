<?php
return [
    'settings' => [
        'displayErrorDetails' => true,

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],
        'dbc' => [
            'username' =>'grupr',
            'password' => 'hunter2',
            'host' => 'localhost',
            'dbname' => 'grupr',
            'db' => 'mysql',

        ],
    ],
];
