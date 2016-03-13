<?php

return [
    // Basic example routes. When controller is used without method (as string),
    // it needs to have a magic __invoke method defined
    ['GET', '/', 'Standard\Controllers\HomeController'],
    ['GET', '/extra', ['Standard\Controllers\ExtraController', 'indexAction']],

    // Authentication routes (sign up / log in)
    ['GET', '/auth', ['Standard\Controllers\AuthController', 'index']],
    ['GET', '/logout', ['Standard\Controllers\AuthController', 'logout']],
    ['GET', '/resetpass/code/{code}/{email}', ['Standard\Controllers\AuthController', 'resetPass']],
    ['POST', '/resetpass', ['Standard\Controllers\AuthController', 'processResetPass']],
    [
        'POST',
        '/auth/signup',
        ['Standard\Controllers\AuthController', 'processSignup']
    ],
    [
        'POST',
        '/auth/login',
        ['Standard\Controllers\AuthController', 'processLogin']
    ],
    [
        ['GET', 'POST'],
        '/auth/forgotpass',
        ['Standard\Controllers\AuthController', 'forgotPassword']
    ],

    ['GET', '/account', 'Standard\Controllers\AccountController'],
    ['GET', '/account/index', ['Standard\Controllers\AccountController', 'indexAction']],

    /* Comment the line below if you don't want to use Glide's dynamic
     * image generation (see http://www.sitepoint.com/easy-dynamic-on-demand-image-resizing-with-glide) */
    ['GET', '/static/image/{image}', ['Standard\Controllers\ImageController', 'renderImage']],
    ['GET', '/image/demo', ['Standard\Controllers\ImageController', 'demo']],

    // User management routes
    ['GET', '/users', 'Standard\Controllers\UsersController'],
    ['GET', '/users/add[/{id}]', ['Standard\Controllers\UsersController', 'upsertUser']],
    ['POST', '/users/add', ['Standard\Controllers\UsersController', 'upsertUserProcess']],
    ['GET', '/users/groups', ['Standard\Controllers\UsersController', 'listGroups']],
    ['GET', '/users/groups/add[/{id}]', ['Standard\Controllers\UsersController', 'upsertGroup']],
    ['POST', '/users/groups/add', ['Standard\Controllers\UsersController', 'upsertGroupProcess']],
    ['POST', '/users/forcelogin', ['Standard\Controllers\UsersController', 'logInAs']],
    ['GET', '/users/exitsuper', ['Standard\Controllers\UsersController', 'exitSuper']],
    ['GET', '/users/delete/{id}', ['Standard\Controllers\UsersController', 'deleteUser']],
    ['GET', '/users/groups/delete/{id}', ['Standard\Controllers\UsersController', 'deleteGroup']],

];