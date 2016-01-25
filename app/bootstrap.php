<?php
/**
 * The bootstrap file creates and returns the container.
 */
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions(__DIR__ . '/config.php');
$containerBuilder->useAnnotations(true);
$container = $containerBuilder->build();
return $container;