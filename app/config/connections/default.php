<?php

/**
 * Default database connection.
 *
 * This is different from the Gatekeeper database that's bootstrapped for user
 * management. This database is for your app, the GK one is for your users.
 *
 * They CAN be the same, but be mindful of table name clashes.
 *
 * Access credentials are changed in the `.env` file in the root of the app.
 */

Cake\Datasource\ConnectionManager::config('default', [
    'className' => 'Cake\Database\Connection',
    'driver' => 'Cake\Database\Driver\Mysql',
    'persistent' => false,
    'host' => getenv('MAIN_DB_HOST'),
    'username' => getenv('MAIN_DB_USER'),
    'password' => getenv('MAIN_DB_PASS'),
    'database' => getenv('MAIN_DB_NAME'),
    'encoding' => 'utf8',
    'timezone' => 'CET',
    'cacheMetadata' => false,
]);
