<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return [
    'basePath'   => __DIR__ . DIRECTORY_SEPARATOR . '..',
    'name'       => 'Converter',
    // preloading 'log' component
    'preload'    => ['log'],
    // autoloading model and component classes
    'import'     => [
        'application.models.*',
        'application.components.*',
    ],
    'modules'    => [
        // uncomment the following to enable the Gii tool
        'gii'=>[
            'class' => 'system.gii.GiiModule',
            'password' => '1',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters'=>['127.0.0.1','::1'],
        ],
    ],
    // application components
    'components' => [
        'user'         => [
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ],
        'urlManager'=>[
            'showScriptName' => false,
            'urlFormat' => 'path',
            'rules' => [
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],
        'db_src' => [
            'class'            => 'CDbConnection',
            'connectionString' => 'pgsql:host=localhost;port=5432;dbname=krasnodar',
            'emulatePrepare' => true,
            'username' => 'postgres',
            'password' => '1234',
            'charset' => 'utf8',
        ],
        'db_dst' => [
            'class'            => 'CDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=fc',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '1234',
            'charset' => 'utf8',
        ],
        'errorHandler' => [
            // use 'site/error' action to display errors
            'errorAction' => 'site/error',
        ],
        'log'          => [
            'class'  => 'CLogRouter',
            'routes' => [
                [
                    'class'  => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ]
            ],
        ],
    ],
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'     => [
        // this is used in contact page
        'adminEmail' => 'nnrudakov@gmail.com',
    ],
];
