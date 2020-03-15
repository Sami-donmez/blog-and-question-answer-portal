<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'mailer' => [
            'host' => 'mail.yoncu.com',
            'username' => 'info@pythonhacisi.com',
            'password' => 'Sami148.148',
            'secure'=>"ssl",
            'port'=>465
          ],
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'data',
            'username' => 'root',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],'dbdev' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'pythonha_fu',
            'username' => 'pythonha_b',
            'password' => '21439278310.',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
        'url'=>'http://localhost:8080/fuidealltema/tema'
    ],
];
