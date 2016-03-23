<?php

Cake\Datasource\ConnectionManager::config('users', [
    'className' => 'Cake\Database\Connection',
    'driver' => 'Cake\Database\Driver\Mysql',
    'persistent' => false,
    'host' => getenv('DB_HOST'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASS'),
    'database' => getenv('DB_NAME'),
    'encoding' => 'utf8',
    'timezone' => 'UTC',
    'cacheMetadata' => false,
]);