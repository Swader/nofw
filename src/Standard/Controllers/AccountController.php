<?php

namespace Standard\Controllers;

use Psecio\Gatekeeper\UserModel;
use Standard\Abstracts\Controller;
use Twig_Environment;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;
use Psecio\Gatekeeper\Gatekeeper;

/**
 * Class AccountController
 * @package Standard\Controllers
 *
 * @auth-groups users
 */
class AccountController extends Controller
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @Inject("User")
     * @var UserModel
     */
    private $user;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Example of an invokable class, i.e. a class that has an __invoke() method.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.invoke
     */
    public function __invoke()
    {
        echo $this->twig->render('account/home.twig', [
            'user' => [
                'firstName' => $this->user->firstName,
                'lastName' => $this->user->lastName
            ]
        ]);
    }

    /**
     * @auth-groups users
     */
    public function indexAction()
    {
        echo $this->twig->render('account/index.twig', [
            'message' => 'Hello from account, index action',
        ]);
    }

    /**
     * @auth-groups users
     */
    public function saveAction()
    {
        if (!empty($_POST['password_new'])) {
            try {
                v::length(6)->check($_POST['password_new']);
            } catch (ValidationException $e) {
                $this->flasher->error(
                    'Please make sure new password is longer than 6 characters!'
                );
            }

            if ($_POST['password_new'] !== $_POST['password_new_confirm']) {
                $this->flasher->error(
                    'New password fields were not identical!'
                );
            }

            if (!Gatekeeper::authenticate([
                'username' => $this->user->username,
                'password' => $_POST['password_old']
            ])) {
                $this->flasher->error('Invalid password. Changes ignored.');
            } else {
                $this->user->password = $_POST['password_new'];
                $this->user->save();
                $this->flasher->success('Password updated!');
            }
        }

        if ($_POST['firstname'] != '-') {
            try {
                v::alnum(' ')->check($_POST['firstname']);
                $this->user->firstName = $_POST['firstname'];
                $this->user->save();
                $this->flasher->success('First name changed.');
            } catch (ValidationException $e) {
                $this->flasher->error(
                    'Name contains invalid characters. '.$e->getMainMessage()
                );
            }
        }

        if ($_POST['lastname'] != '-') {
            try {
                v::alnum(' ')->check($_POST['lastname']);
                $this->user->lastName = $_POST['lastname'];
                $this->user->save();
                $this->flasher->success('Last name changed.');
            } catch (ValidationException $e) {
                $this->flasher->error(
                    'Last name contains invalid characters. '.$e->getMainMessage()
                );
            }
        }

        $this->redirect('/account');
    }
}
