<?php

use function DI\object;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psecio\Gatekeeper\Gatekeeper;
use SitePoint\Rauth;
use Tamtamchik\SimpleFlash\Flash;
use Tamtamchik\SimpleFlash\Templates\Foundation6Template;

$user = null;
if (isset($_SESSION['user'])) {
    $user = Gatekeeper::findUserByUsername($_SESSION['user']);
    if (!$user) {
        session_destroy();
        unset($_SESSION['user']);
        header('Location: /');
        die();
    }
}

$shared = [
    'site' => [
        'name' => 'Skeleton',
        'url' => 'http://test.app',
        'sender' => 'skeleton@example.app',
        'replyto' => 'skeleton@example.app',
        'debug' => (bool)getenv('DEBUG'),
    ],
    'user' => $user,
];

return [

    'mailgun-config' => [
        'key' => getenv('MAILGUN_KEY'),
        'domain' => getenv('MAILGUN_DOMAIN'),
    ],

    'site-config' => $shared['site'],

    // Configure Twig
    Twig_Environment::class => function (Flash $flash) use ($shared) {
        $loader = new Twig_Loader_Filesystem(
            __DIR__ . '/../src/Standard/Views'
        );

        $te = new Twig_Environment($loader);

        $te->addGlobal('site', $shared['site']);

        if ($shared['user']) {
            $te->addGlobal('username', $shared['user']->username);
        }

        if ($flash->hasMessages()) {
            $te->addGlobal('flashes', $flash->display());
        }

        return $te;
    },

    'glide' => function () {
        $server = League\Glide\ServerFactory::create(
            [
                'source' => new League\Flysystem\Filesystem(
                    new League\Flysystem\Adapter\Local(
                        __DIR__ . '/../assets/image'
                    )
                ),
                'cache' => new League\Flysystem\Filesystem(
                    new League\Flysystem\Adapter\Local(
                        __DIR__ . '/../public/static/image'
                    )
                ),
                'driver' => 'gd',
            ]
        );

        return $server;
    },

    ClientInterface::class => function () {
        $client = new Client();

        return $client;
    },

    Flash::class => function () {
        return new Flash(new Foundation6Template());
    },

    'User' => function () use ($shared) {
        return $shared['user'];
    },

    'rauth' => function () {
        $rauth = new Rauth();

        // Add cache at some point
        return $rauth;
    },
];