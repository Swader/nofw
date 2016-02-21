<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use SitePoint\Rauth;
use Tamtamchik\SimpleFlash\Flash;

/** @var Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';
// Gatekeeper initalization and configuration
use \Psecio\Gatekeeper\Gatekeeper;

Gatekeeper::init('../');

$routeList = require '../app/routes.php';

/** @var Dispatcher $dispatcher */
$dispatcher = FastRoute\simpleDispatcher(
    function (RouteCollector $r) use ($routeList) {
        foreach ($routeList as $routeDef) {
            $r->addRoute($routeDef[0], $routeDef[1], $routeDef[2]);
        }
    }
);

$route = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

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

        /** @var Rauth $rauth */
        $rauth = $container->get('rauth');
        $attr = [
            'groups' => [],
            'permissions' => [],
        ];
        $user = $container->get('User');
        if ($user) {
            foreach ($user->groups as $group) {
                $attr['groups'][] = $group->name;
                foreach ($group->children as $subGroup) {
                    $attr['groups'][] = $subGroup->name;
                }
            }
            $attr['groups'] = array_unique($attr['groups']);
            foreach ($user->permissions as $permission) {
                $attr['permissions'][] = $permission->name;
            }
        }
        //dump($attr);
        try {
            $ctrl = (is_array($controller))
                ? $container->get($controller[0])
                : $container->get($controller);

            $method = (is_array($controller)) ? $controller[1] : '__invoke';

            $rauth->authorize($ctrl, $method, $attr);
        } catch (Rauth\Exception\AuthException $e) {
            $flasher = $container->get(Flash::class);
            if ($container->get('site-config')['debug'] === true) {
                $flasher->error('Failed due to: ' . $e->getType());
                /** @var Rauth\Exception\Reason $reason */
                foreach ($e->getReasons() as $reason) {
                    $m = 'Blocked by "' . $reason->group . '" when comparing owned "' . implode(
                            ", ", $reason->has
                        ) . '" versus "' . implode(", ", $reason->needs) . '".';
                    $flasher->error($m);
                }
            } else {
                $flasher->error('Authorization failed.');
            }
            header('Location: /');
            die();
        }

        $container->call($controller, $parameters);
        break;
}