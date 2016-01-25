<?php

use function DI\object;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

$shared = [
    'site' => [
        'name' => 'Skeleton',
        'url' => 'http://test.app',
        'sender' => 'skeleton@example.app',
        'replyto' => 'skeleton@example.app'
    ]
];

return [

    'mailgun-config' => [
        'key' => getenv('MAILGUN_KEY'),
        'domain' => getenv('MAILGUN_DOMAIN')
    ],

    'site-config' => $shared['site'],

    // Configure Twig
    Twig_Environment::class => function () use ($shared) {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../src/Standard/Views');

        $te = new Twig_Environment($loader);

        $te->addGlobal('site', $shared['site']);

        return $te;
    },

    "CI" => function () {
        $client = new Client();
        return $client;
    },

    ClientInterface::class => function () {
        $client = new Client();
        return $client;
    }
];