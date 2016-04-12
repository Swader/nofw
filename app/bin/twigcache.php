<?php

require __DIR__.'/../../vendor/autoload.php';
$shared = require __DIR__.'/../config/shared/root.php';

$tplDir = dirname(__FILE__) . '/templates';
$tmpDir = '/tmp/cache/';
$loader = new Twig_Loader_Filesystem($shared['site']['viewsFolders']);

// force auto-reload to always have the latest version of the template
$twig = new Twig_Environment(
    $loader, [
    'cache' => $tmpDir,
    'auto_reload' => true,
]
);
$twig->addExtension(new Twig_Extensions_Extension_I18n());
// configure Twig the way you want

// iterate over all your templates
foreach ($shared['site']['viewsFolders'] as $tplDir) {
    foreach (new RecursiveIteratorIterator(
                 new RecursiveDirectoryIterator($tplDir),
                 RecursiveIteratorIterator::LEAVES_ONLY
             ) as $file) {
        // force compilation
        if ($file->isFile()) {
            $twig->loadTemplate(str_replace($tplDir . '/', '', $file));
        }
    }
}