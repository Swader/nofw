<?php

return [
    'site' => [
        'name' => getenv('APP_NAME') ?: 'Skeleton app',
        'url' => getenv('APP_URL') ?: 'http://test.app',
        'sender' => getenv('APP_SENDER') ?: 'skeleton@example.app',
        'replyto' => getenv('APP_REPLYTO') ?: 'skeleton@example.app',
        'debug' => getenv('DEBUG') === 'true',
        'env' => getenv('APPLICATION_ENV'),
        'logFolder' => __DIR__.'/../../../logs',
        'viewsFolders' => [__DIR__.'/../../../src/Standard/Views']
    ],
    'cron' => [
        'emails' => false
    ],
    'mailgun-config' => [
        'key' => getenv('MAILGUN_KEY'),
        'domain' => getenv('MAILGUN_DOMAIN'),
        'smtpUsername' => getenv('MAILGUN_SMTP_LOGIN'),
        'smtpPassword' => getenv('MAILGUN_SMTP_PASS'),
        'smtpHost' => getenv('MAILGUN_SMTP_HOST'),
    ]
];