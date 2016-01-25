<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

/** @var Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

// Gatekeeper initalization and configuration
use \Psecio\Gatekeeper\Gatekeeper;
Gatekeeper::init('../');

/** @var Dispatcher $dispatcher */
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    foreach (require '../app/routes.php' as $routeDef) {
        $r->addRoute($routeDef[0], $routeDef[1], $routeDef[2]);
    }
});

$route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']);

switch ($route[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo $container->get(Twig_Environment::class)->render('error404.twig');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        echo $container->get(Twig_Environment::class)->render('error405.twig');
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller = $route[1];
        $parameters = $route[2];

        $container->call($controller, $parameters);
        break;
}