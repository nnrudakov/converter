<?php

Yii::setPathOfAlias('accordance', __DIR__ . '/../components/converters/accordance');

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return [
    'basePath'   => __DIR__ . DIRECTORY_SEPARATOR . '..',
    'name'       => 'Converter',
    // preloading 'log' component
    'preload'    => ['log'],
    // autoloading model and component classes
    'import'     => [
        'application.models.BaseFcModel',
        'application.models.src.*',
        'application.models.dst.*',
        'application.components.*',
        'application.components.converters.*',
    ],
    // application components
    'components' => [
        'db_src' => [
            'class'            => 'CDbConnection',
            'connectionString' => 'pgsql:host=mail.fckrasnodar.ru;port=5432;dbname=krasnodar',
            'emulatePrepare' => true,
            'username' => 'postgres',
            'password' => 'W1CWDhFmt1W9uElLy2TpixOVQgqHCE',
            'charset' => 'utf8',
            'enableParamLogging' => true,
            'enableProfiling' => true,
            'persistent' => true
        ],
        'db_media' => [
            'class'            => 'CDbConnection',
            'connectionString' => 'pgsql:host=mail.fckrasnodar.ru;port=5432;dbname=krasnodarmedia',
            'emulatePrepare' => true,
            'username' => 'postgres',
            'password' => 'W1CWDhFmt1W9uElLy2TpixOVQgqHCE',
            'charset' => 'utf8',
            'enableParamLogging' => true,
            'enableProfiling' => true,
            'persistent' => true
        ],
        'db_dst' => [
            'class'            => 'CDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=fc',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '1234',
            'charset' => 'utf8',
            'tablePrefix' => 'fc__',
            'enableParamLogging' => true,
            'enableProfiling' => true,
            'persistent' => true
        ],
        'log' => [
            'class'  => 'CLogRouter',
            'routes' => [
                [
                    'class'  => 'CFileLogRoute',
                    'levels' => 'error, warning, trace, info',
                ],
            ],
        ],
    ],
    'params'     => [
        'files_dir' => '/home/rudnik/www/fc/files/',
    ]
];
