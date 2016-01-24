<?php

use function DI\object;

return [
    // Configure Twig
    Twig_Environment::class => function () {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../src/Standard/Views');

        return new Twig_Environment($loader);
    },
];