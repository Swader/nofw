<?php

/**
 * This file is consumed by the ContainerBuilder in command-line entry points.
 *
 * For example, these definitions are loaded when crons are run, but they can
 * also be used for when any command line script needs to run. See `cron.php`
 */

use Monolog\ErrorHandler;
use SitePoint\Rauth;
use Tamtamchik\SimpleFlash\Templates;
use Psr\Log\LoggerInterface as Logger;

$d = new Dotenv\Dotenv(__DIR__ . '/../../');
$d->load();

$shared = require_once __DIR__ . '/shared/root.php';
require_once __DIR__ . '/connections/default.php';
require_once __DIR__ . '/connections/users.php';

return [

    'mailgun-config' => $shared['mailgun-config'],
    'site-config' => $shared['site'],
    'cron-config' => $shared['cron'],

    'glide' => require_once __DIR__ . '/shared/glide.php',

    GuzzleHttp\ClientInterface::class => function () {
        $client = new GuzzleHttp\Client();

        return $client;
    },

    'rauth' => function () {
        $rauth = new Rauth();

        // Add cache at some point
        return $rauth;
    },

    Logger::class => function () use ($shared) {
        $logger = new \Monolog\Logger('clilog');

        $logger->pushHandler(
            new Monolog\Handler\StreamHandler(
                $shared['site']['logFolder'] . '/all-cli.log'
            )
        );
        $logger->pushHandler(
            new Monolog\Handler\StreamHandler(
                $shared['site']['logFolder'] . '/error.log',
                \Monolog\Logger::NOTICE
            )
        );

        ErrorHandler::register($logger);

        return $logger;
    },
];
