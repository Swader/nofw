<?php

namespace Standard\Controllers;

use GuzzleHttp\ClientInterface;
use Psecio\Gatekeeper\Exception\UserInactiveException;
use Psecio\Gatekeeper\Gatekeeper;
use Psecio\Gatekeeper\UserModel;
use Respect\Validation\Exceptions\ValidationException;
use Standard\Abstracts\Controller;
use Tamtamchik\SimpleFlash\Flash;
use Twig_Environment;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @Inject("mailgun-config")
     */
    private $mailgun_config;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @auth-groups users
     * @auth-mode none
     */
    public function index()
    {
        echo $this->twig->render('auth/index.twig');
    }

    protected function initGroups()
    {
        $groups = [
            'users' => 'Regular users',
            'admin' => 'Administrators',
        ];

        $adminGroup = null;
        foreach ($groups as $name => $description) {
            if (!Gatekeeper::findGroupByName($name)) {
                Gatekeeper::createGroup(
                    [
                        'name' => $name,
                        'description' => $description,
                    ]
                );
            }
        }
    }

    public function processSignup()
    {

        try {
            v::email()->check($_POST['email']);
            v::length(6)->check($_POST['password']);
        } catch (ValidationException $e) {
            $this->flasher->error(
                'Please make sure your password is longer than 6 characters, and that your username is a valid email address!'
            );
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            $this->flasher->error('Passwords need to be identical');
        }

        if ($this->flasher->hasMessages('error')) {
            $this->redirect('/auth');
        }

        $this->initGroups();

        // Create an account if none exists
        $user = Gatekeeper::register(
            [
                'first_name' => '-',
                'last_name' => '-',
                'username' => $_POST['email'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'groups' => (Gatekeeper::countUser()) ? ['users'] : [
                    'admin',
                    'users',
                ],
            ]
        );

        if ($user) {
            $this->flasher->success(
                'Account successfully registered! Please log in!'
            );
        } else {
            $this->flasher->error(
                'Error #GK01: Account creation failed!' . Gatekeeper::getDatasource(
                )->getLastError()
            );
        }
        $this->redirect('/auth');
    }

    public function processLogin()
    {
        $success = false;
        try {
            $success = Gatekeeper::authenticate(
                [
                    'username' => $_POST['email'],
                    'password' => $_POST['password'],
                ]
            );
        } catch (\Exception $e) {
            $this->flasher->error(($this->site['debug']) ? $e->getMessage() : 'Something went wrong');
            $this->redirect('/auth');
        }

        if ($success) {
            $_SESSION['user'] = $_POST['email'];
            $this->redirect('/');
        } else {
            $this->flasher->error('Invalid credentials!');
            $this->redirect('/auth');
        }
    }

    public function logout()
    {
        session_destroy();
        unset($_SESSION['user']);
        $this->redirect('/');
    }

    /**
     * @param ClientInterface $client
     */
    public function forgotPassword(ClientInterface $client)
    {

        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                echo $this->twig->render('auth/forgotpass.twig');
                break;
            case "POST":
                $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                if ($email) {

                    $response = false;

                    // FIND ACCOUNT
                    $user = Gatekeeper::findUserByEmail($email);

                    // Ignore use case when user isn't found.
                    // We don't want to reveal that someone has an email in our database
                    if ($user) {

                        // GENERATE CODE
                        $code = $user->getResetPasswordCode();

                        // GENERATE HTML
                        $html = $this->twig->render(
                            'emails/forgotpass.twig',
                            ['code' => $code, 'email' => base64_encode($email)]
                        );

                        // CONFIGURE SENDER AND URL
                        $replyto = $this->site_config['replyto'] ?? $this->site_config['sender'];
                        $url = 'https://api.mailgun.net/v3/' . $this->mailgun_config['domain'] . '/messages';

                        // SEND EMAIL
                        $response = $client->request(
                            'POST', $url, [
                                'auth' => ['api', $this->mailgun_config['key']],
                                'multipart' => [
                                    ['name' => 'to', 'contents' => $email],
                                    ['name' => 'from', 'contents' => $replyto],
                                    [
                                        'name' => 'subject',
                                        'contents' => 'Forgot your password?',
                                    ],
                                    ['name' => 'html', 'contents' => $html],
                                ],
                            ]
                        );
                    }

                    if ($response && $response->getStatusCode() == "200") {
                        // REDIRECT
                        $this->flasher->info(
                            'Password reset email is on its way!'
                        );
                    } else {
                        $this->flasher->error('Email could not be sent :(');
                    }

                    $this->redirect('/auth');

                } else {
                    $this->flasher->error('Invalid email provided!');
                    $this->redirect('/auth/forgotpass');
                }
                break;
            default:
                echo $this->twig->render('error405.twig');
        }

    }

    public function resetPass($code, $email)
    {
        $email = base64_decode($email);
        /** @var UserModel $user */
        $user = Gatekeeper::findUserByEmail($email);

        $check = false;
        try {
            $check = $user->checkResetPasswordCode($code);
        } catch (\Exception $e) {
            $this->flasher->error($e->getMessage());
            $this->redirect('/');
        }

        if (!$code || !$user || !$check) {
            $this->flasher->error('Invalid code!');
            $this->redirect('/');
        }

        $_SESSION['user'] = $email;
        echo $this->twig->render('auth/resetpass.twig');
    }

    public function processResetPass()
    {
        /** @var UserModel $user */
        $user = Gatekeeper::findUserByEmail($_SESSION['user']);
        if (!$user) {
            $this->flasher->error('Password reset session expired');
            unset($_SESSION['user']);
            $this->redirect('/');
        }
        if ($_POST['password'] == $_POST['password_confirm']) {
            $user->password = $_POST['password'];
            if ($user->save()) {
                $this->flasher->success('Successfully changed password!');
            } else {
                $this->flasher->error('Could not update password :(');
            }
            $this->redirect('/');
        }
    }
}
