<?php

use function DI\object;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Psecio\Gatekeeper\Gatekeeper;
use SitePoint\Rauth;
use Tamtamchik\SimpleFlash\Flash;
use Tamtamchik\SimpleFlash\FlashInterface;
use Tamtamchik\SimpleFlash\TemplateFactory;
use Tamtamchik\SimpleFlash\Templates;
use Psr\Log\LoggerInterface as Logger;

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
        'debug' => getenv('DEBUG') === 'true',
        'env' => getenv('ENVIRONMENT'),
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
    Twig_Environment::class => function (FlashInterface $flash) use ($shared) {
        $loader = new Twig_Loader_Filesystem(
            __DIR__ . '/../src/Standard/Views'
        );

        $te = new Twig_Environment($loader);

        $te->addGlobal('site', $shared['site']);

        if ($shared['user']) {
            $te->addGlobal('username', $shared['user']->username);
        }

        if (isset($_SESSION['superuser'])) {
            $te->addGlobal('super', $_SESSION['superuser']);
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
        return new Flash(TemplateFactory::create(Templates::FOUNDATION_6));
    },

    'User' => function () use ($shared) {
        return $shared['user'];
    },

    'rauth' => function () {
        $rauth = new Rauth();

        // Add cache at some point
        return $rauth;
    },

    Logger::class => function () use ($shared) {
        $logger = new \Monolog\Logger('nofwlog');

        $logger->pushHandler(new StreamHandler(__DIR__.'/../logs/all.log'));
        if ($shared['site']['env'] == 'dev') {
            $logger->pushHandler(new BrowserConsoleHandler());
        }

        $logger->info('Logging set up');

        return $logger;
    },
];
