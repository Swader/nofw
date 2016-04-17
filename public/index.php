<?php

/**
 * The main bootstrap file.
 *
 * It loads the DI configurations `app/config/config_web.php`, sets up routes,
 * and then calls the controller defined in the routes.
 *
 * Autowiring and annotation use is on, so easy dependency injection is enabled
 * by default.
 *
 * This file also checks for authorization using Rauth to make sure the current
 * user has permissions to run a controller/method. For more information on that
 * @see http://www.sitepoint.com/control-user-access-to-classes-and-methods-with-rauth/
 */

define('ROOT', realpath(__DIR__.'/..'));
session_start();

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Log\LoggerInterface;
use SitePoint\Rauth;
use Tamtamchik\SimpleFlash\Flash;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder;
$container = $containerBuilder
    ->addDefinitions(require_once __DIR__ . '/../app/config/config_web.php')
    ->useAnnotations(true)
    ->build();

$routeList = require __DIR__.'/../app/routes.php';

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

        try {
            $ctrl = (is_array($controller))
                ? $container->get($controller[0])
                : $container->get($controller);

            $method = (is_array($controller)) ? $controller[1] : '__invoke';

            $rauth->authorize($ctrl, $method, $attr);

        } catch (Rauth\Exception\AuthException $e) {
            $logger = $container->get(LoggerInterface::class)->error('Omg');
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