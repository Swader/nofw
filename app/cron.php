<?php

/**
 * This is the entry point for *adding cronjobs to Jobby*. This file is not
 * meant to be changed unless you're changing your cron runner from Jobby to
 * something else.
 *
 * This is the file to which the only entry in the system's crontab should be
 * pointed.
 *
 * This is also where the dependency injection for Cron tasks is set up. If you
 * look in the try/catch block below, you'll notice another container is built
 * inside the closure, and it consumes the `config_cli.php` file. This is where
 * it gets its definitions. If you want to add more service definitions to your
 * cronjobs, edit `config_cli.php`.
 */

use Cake\ORM\TableRegistry;
use Jobby\Jobby;
use Psr\Log\LoggerInterface as Logger;
use DI\ContainerBuilder;
use Standard\Controllers\Cron\BetterBackgroundJob;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder;
$container = $containerBuilder
    ->addDefinitions(require_once __DIR__ . '/../app/config/config_cli.php')
    ->useAnnotations(true)
    ->build();

try {
    $crons = TableRegistry::get('Cron')->find()->hydrate(false)->toArray();
    $settings = TableRegistry::get('CronSettings')->find()->hydrate(
        false
    )->toArray()[0];
} catch (\Exception $e) {
    $container->get(Logger::class)->error(
        'Error while fetching DB contents: ' . $e->getMessage()
    );

    return;
}

$container->set(
    'jobby', function () use ($container) {
    $j = new Jobby(
        ($container->get('cron-config')['emails']) ?
            [
                'mailer' => 'smtp',
                'smtpUsername' => getenv('MAILGUN_SMTP_LOGIN'),
                'smtpPassword' => getenv('MAILGUN_SMTP_PASS'),
                'smtpHost' => getenv('MAILGUN_SMTP_HOST'),
            ] : []
    );
    $j->setConfig(['jobClass' => BetterBackgroundJob::class]);

    return $j;
}
);

/** @var array $cron */
foreach ($crons as $cron) {
    $cron = array_merge($settings, array_filter($cron));
    $jobName = $cron['name'];
    unset($cron['name']);
    $cron['output'] = ($cron['output'] ?? false)
        ? $container->get('site-config')['logFolder'] . '/' . $cron['output']
        : $container->get('site-config')['logFolder'] . '/cron.log';

    try {
        if (strpos($cron['command'], '::')) {

            // Assuming it's a Class::method syntax
            list($class, $method) = explode('::', $cron['command']);

            $cron['command'] = function () use ($class, $method) {
                $containerBuilder = new ContainerBuilder;
                $container = $containerBuilder
                    ->addDefinitions(require_once __DIR__ . '/../app/config/config_cli.php')
                    ->useAnnotations(true)
                    ->build();
                $container->call([new $class, $method]);

                return true;
            };
        }
        $container->get('jobby')->add($jobName, $cron);
    } catch (\Exception $e) {
        $container->get(Logger::class)->error(
            'Job "' . $cron['name'] . '" could not be added:' . $e->getMessage()
        );
    }
}

try {
    $container->get('jobby')->run();
} catch (\Throwable $e) {
    $container->get(Logger::class)->error(
        'Cronning failed: ' . $e->getMessage()
    );
}