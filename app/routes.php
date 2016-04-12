<?php

/**
 * Define routes here.
 *
 * Routes follow the format:
 *
 * [METHOD, ROUTE, CALLABLE]
 *
 * Routes can use optional segments and regular expressions. See nikic/fastroute
 */

return [
    // Basic example routes. When controller is used without method (as string),
    // it needs to have a magic __invoke method defined
    ['GET', '/', 'Standard\Controllers\HomeController'],
    ['GET', '/extra', ['Standard\Controllers\ExtraController', 'indexAction']],

    // Authentication routes (sign up / log in)
    ['GET', '/auth', ['Standard\Controllers\AuthController', 'indexAction']],
    ['GET', '/logout', ['Standard\Controllers\AuthController', 'logoutAction']],
    ['GET', '/resetpass/code/{code}/{email}', ['Standard\Controllers\AuthController', 'resetPassAction']],
    ['POST', '/resetpass', ['Standard\Controllers\AuthController', 'processResetPassAction']],
    [
        'POST',
        '/auth/signup',
        ['Standard\Controllers\AuthController', 'processSignupAction']
    ],
    [
        'POST',
        '/auth/login',
        ['Standard\Controllers\AuthController', 'processLoginAction']
    ],
    [
        ['GET', 'POST'],
        '/auth/forgotpass',
        ['Standard\Controllers\AuthController', 'forgotPasswordAction']
    ],

    ['GET', '/account', 'Standard\Controllers\AccountController'],
    ['GET', '/account/index', ['Standard\Controllers\AccountController', 'indexAction']],
    ['POST', '/account/save', ['Standard\Controllers\AccountController', 'saveAction']],

    /* Comment the line below if you don't want to use Glide's dynamic
     * image generation (see http://www.sitepoint.com/easy-dynamic-on-demand-image-resizing-with-glide) */
    ['GET', '/static/image/{image}', ['Standard\Controllers\ImageController', 'renderImageAction']],
    ['GET', '/image/demo', ['Standard\Controllers\ImageController', 'demoAction']],

    // User management routes
    ['GET', '/users', 'Standard\Controllers\UsersController'],
    ['GET', '/users/add[/{id}]', ['Standard\Controllers\UsersController', 'upsertUserAction']],
    ['POST', '/users/add', ['Standard\Controllers\UsersController', 'upsertUserProcessAction']],
    ['GET', '/users/groups', ['Standard\Controllers\UsersController', 'listGroupsAction']],
    ['GET', '/users/groups/add[/{id}]', ['Standard\Controllers\UsersController', 'upsertGroupAction']],
    ['POST', '/users/groups/add', ['Standard\Controllers\UsersController', 'upsertGroupProcessAction']],
    ['POST', '/users/forcelogin', ['Standard\Controllers\UsersController', 'logInAsAction']],
    ['GET', '/users/exitsuper', ['Standard\Controllers\UsersController', 'exitSuperAction']],
    ['GET', '/users/delete/{id}', ['Standard\Controllers\UsersController', 'deleteUserAction']],
    ['GET', '/users/groups/delete/{id}', ['Standard\Controllers\UsersController', 'deleteGroupAction']],

    ['GET', '/admin/crons', ['Standard\Controllers\Cron\CronController', 'listCrons']],
    ['GET', '/admin/crons/add', ['Standard\Controllers\Cron\CronController', 'upsertCronGet']],
    ['GET', '/admin/crons/edit/{id}', ['Standard\Controllers\Cron\CronController', 'upsertCronGet']],
    ['POST', '/admin/crons/upsert', ['Standard\Controllers\Cron\CronController', 'upsertCronPost']],
    ['POST', '/admin/crons/settings', ['Standard\Controllers\Cron\CronController', 'saveSettings']],
    ['POST', '/admin/crons/delete', ['Standard\Controllers\Cron\CronController', 'deleteCron']],

];