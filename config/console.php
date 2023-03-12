<?php

$local = file_exists(__DIR__ . '/local.php') ? require(__DIR__ . '/local.php') : [];
$config = require __DIR__ . '/app.php';
$config['controllerNamespace'] = 'app\commands';
$config['components']['request'] = [
    'class' => \app\core\ConsoleRequest::class,
];

$config['components']['log'] = [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'app\helpers\ConsoleLog',
            'levels' => ['error', 'warning',],
            'logVars' => ['_GET', '_POST', '_FILES',],
        ],
    ],
];

return $config;
