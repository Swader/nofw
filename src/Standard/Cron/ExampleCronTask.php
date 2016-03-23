<?php

namespace Standard\Cron;

use Psr\Log\LoggerInterface;

class ExampleCronTask
{
    public function helloWorld(LoggerInterface $l)
    {
        // This will log a message into `/logs/all-cli.log`, the logger
        // as defined in `app/config/config_cli.php`
        $l->info("I am here");
    }
}