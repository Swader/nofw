<?php

/**
 * This file is consumed when the web side of the app is being loaded.
 *
 * These definitions will be available in the app's controllers etc.
 */

use function DI\object;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Monolog\ErrorHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Psecio\Gatekeeper\Gatekeeper;
use SitePoint\Rauth;
use Tamtamchik\SimpleFlash\Flash;
use Tamtamchik\SimpleFlash\TemplateFactory;
use Tamtamchik\SimpleFlash\Templates;
use Psr\Log\LoggerInterface as Logger;

Gatekeeper::init(__DIR__ . '/../../');
Gatekeeper::disableThrottle();

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

if (getenv('INTL') == 'true') {
    $language = getenv('INTL_LANG');
    putenv("LANGUAGE=" . $language);
    setlocale(LC_ALL, $language);

    $domain = "messages"; // which language file to use

    $localeFolder = getenv('LOCALE_FOLDER');
    if (strpos($localeFolder, 'ROOT/')) {
        $localeFolder = str_replace(
            'ROOT/', constant('ROOT') . '/', $localeFolder
        );
    }

    bindtextdomain($domain, $localeFolder);
    bind_textdomain_codeset($domain, 'UTF-8');

    textdomain($domain);
}

$shared = require_once __DIR__ . '/shared/root.php';
$shared['user'] = $user;
require_once __DIR__ . '/connections/default.php';
require_once __DIR__ . '/connections/users.php';

return [

    'mailgun-config' => $shared['mailgun-config'],
    'site-config' => $shared['site'],

    // Configure Twig
    Twig_Environment::class => function (Flash $flash) use ($shared) {
        $loader = new Twig_Loader_Filesystem(
            $shared['site']['viewsFolders']
        );

        $te = new Twig_Environment($loader);

        $te->addExtension(new Twig_Extensions_Extension_I18n());

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

    'glide' => require_once __DIR__ . '/shared/glide.php',

    ClientInterface::class => function () {
        $client = new Client();

        return $client;
    },

    Flash::class => function () {
        return new Flash(TemplateFactory::create(Templates::SEMANTIC_2));
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

        $logger->pushHandler(
            new StreamHandler($shared['site']['logFolder'] . '/all.log')
        );
        $logger->pushHandler(
            new StreamHandler(
                $shared['site']['logFolder'] . '/error.log',
                \Monolog\Logger::NOTICE
            )
        );
        if ($shared['site']['env'] == 'dev') {
            $logger->pushHandler(new BrowserConsoleHandler());
        }

        ErrorHandler::register($logger);

        $logger->info('Logging set up');

        return $logger;
    },
];
