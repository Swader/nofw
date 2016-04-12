<?php

namespace Standard\Controllers;

use Psecio\Gatekeeper\Gatekeeper;
use Psecio\Gatekeeper\GroupCollection;
use Psecio\Gatekeeper\UserModel;
use Respect\Validation\Exceptions\ValidationException;
use Standard\Abstracts\Controller;
use Twig_Environment;
use Respect\Validation\Validator as v;

/**
 * Class UsersController
 * @auth-groups admin
 * @package Standard\Controllers
 */
class UsersController extends Controller
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @Inject("User")
     * @var UserModel|null
     */
    private $user;

    /**
     * @var GroupCollection
     */
    private $gk_groups;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
        $this->gk_groups = Gatekeeper::findGroups();
    }

    public function __invoke()
    {
        $this->listUsers();
    }

    public function listUsers()
    {
        echo $this->twig->render(
            'users/list.twig', ['users' => Gatekeeper::findUsers()]
        );
    }

    public function upsertUserAction($id = null)
    {
        $data = ['groups' => $this->gk_groups, 'groupnames' => []];
        if ($id) {
            $user = Gatekeeper::findUserById($id);
            if (!$user) {
                $this->flasher->error('User with ID ' . $id . ' not found!');
                $this->redirect('/users');
            }
            $data['user'] = $user;
            foreach ($user->groups as $group) {
                $data['groupnames'][] = $group->name;
            }
        }
        echo $this->twig->render('users/upsert.twig', $data);
    }

    public function upsertUserProcessAction()
    {
        $id = $_POST['id'] ?? null;

        if ($id && !ctype_digit($id)) {
            $this->flasher->error('E01 Invalid user ID: ' . $id);
            $this->redirect('/users');
        }

        $user = null;
        if ($id) {
            $user = Gatekeeper::findUserById($id);
            if (!$user) {
                $this->flasher->error('E02 Invalid user ID: ' . $id);
            }
        }

        // Validation
        try {
            v::alnum('- .')->setName('First name')->check($_POST['firstName']);
            v::alnum('- .')->setName('Last name')->check($_POST['lastName']);
            v::email()->setName('Email')->check($_POST['email']);
            if (!$user) {
                v::notEmpty()->setName('Password')->check($_POST['password']);
            }
            $_POST['username'] = $_POST['email'];
            $_POST['groups'] = array_map('intval', array_filter($_POST['groups']));
        } catch (ValidationException $e) {
            $this->flasher->error($e->getMainMessage());
            echo $this->twig->render(
                'users/upsert.twig',
                [
                    'flashes' => $this->flasher->display(),
                    'user' => ($user) ?: $_POST,
                    'groups' => $this->gk_groups
                ]
            );

            return false;
        }

        if ($user) {
            $user->firstName = $_POST['firstName'];
            $user->lastName = $_POST['lastName'];
            $user->email = $_POST['email'];
            $user->username = $_POST['email'];
            if (!empty($_POST['password'])) {
                $user->password = $_POST['password'];
            }
            $user->save();

            foreach ($user->groups as $group) {
                $user->revokeGroup($group->id);
            }
            foreach ($_POST['groups'] as $group) {
                $user->addGroup($group);
            }

            ((bool)$_POST['active'] ?? false) ? $user->activate(
            ) : $user->deactivate();
            $this->flasher->success('Successfully updated user.');
            $this->redirect('/users/add/' . $user->id);
        } else {
            $groups = $_POST['groups'];
            unset($_POST['groups']);
            if ($user = Gatekeeper::register($_POST)) {
                foreach ($groups as $group) {
                    $user->addGroup($group);
                }
            }
            if (Gatekeeper::getLastError()) {
                $this->flasher->error(
                    ($this->site['debug']) ? Gatekeeper::getLastError(
                    ) : "Could not create user!"
                );
                echo $this->twig->render(
                    'users/upsert.twig',
                    [
                        'flashes' => $this->flasher->display(),
                        'user' => ($user) ?: $_POST,
                        'groups' => $this->gk_groups
                    ]
                );

                return false;
            }
            $this->flasher->success('Successfully created user.');
            $this->redirect('/users');
        }

        return true;
    }

    public function listGroupsAction()
    {
        echo $this->twig->render(
            'users/groups/list.twig', ['groups' => $this->gk_groups]
        );
    }

    public function upsertGroupAction($id = null)
    {
        $data = [];
        if ($id) {
            $group = Gatekeeper::findGroupById($id);
            if (!$group) {
                $this->flasher->error('Group with ID ' . $id . ' not found!');
                $this->redirect('/users/groups');
            }
            $data['group'] = $group;
        }
        echo $this->twig->render('users/groups/upsert.twig', $data);
    }

    public function upsertGroupProcessAction()
    {
        $id = $_POST['id'] ?? null;

        if ($id && !ctype_digit($id)) {
            $this->flasher->error('E01 Invalid group ID: ' . $id);
            $this->redirect('/users/groups');
        }

        $group = null;
        if ($id) {
            $group = Gatekeeper::findGroupById($id);
            if (!$group) {
                $this->flasher->error('E02 Invalid group ID: ' . $id);
            }
        }

        try {
            v::alnum('-._')->setName('Group name')->check($_POST['name']);
        } catch (ValidationException $e) {
            $this->flasher->error($e->getMainMessage());
            echo $this->twig->render(
                'users/groups/upsert.twig',
                [
                    'flashes' => $this->flasher->display(),
                    'group' => ($group) ?: $_POST,
                ]
            );

            return false;
        }

        if ($group) {
            $group->name = $_POST['name'];
            $group->description = $_POST['description'];
            $group->save();
        } else {
            Gatekeeper::createGroup($_POST);
            if (Gatekeeper::getLastError()) {
                $this->flasher->error(
                    ($this->site['debug']) ? Gatekeeper::getLastError(
                    ) : "Could not create group!"
                );
                echo $this->twig->render(
                    'users/groups/upsert.twig',
                    [
                        'flashes' => $this->flasher->display(),
                        'user' => ($group) ?: $_POST,
                    ]
                );

                return false;
            }
            $this->flasher->success('Successfully created group.');
            $this->redirect('/users/groups');
        }

    }

    /**
     * Only admins can access this method
     */
    public function logInAsAction()
    {
        if (!ctype_digit($_POST['id'])) {
            $this->flasher->error(
                'User ID is invalid - not a number: ' . $_POST['id']
            );
            $this->redirect('/users');
        }

        $logInAs = Gatekeeper::findUserById($_POST['id']);
        if ($logInAs && !$logInAs->inGroup('admin')) {
            $_SESSION['superuser'] = $this->user->username;
            $_SESSION['user'] = $logInAs->username;
            $this->flasher->success(
                'Successfully logged in as ' . $logInAs->username
            );
        } else {
            $this->flasher->error(
                'Cannot log in as user with ID ' . $_POST['id']
            );
        }
        $this->redirect('/');
    }

    /**
     * @auth-groups users
     */
    public function exitSuperAction()
    {
        if (isset($_SESSION['superuser'])) {
            $_SESSION['user'] = $_SESSION['superuser'];
            unset($_SESSION['superuser']);
            $this->flasher->success('You are logged in as admin again.');
        }
        $this->redirect('/');
    }

    public function deleteUserAction(int $id)
    {
        if (Gatekeeper::deleteUserById($id)) {

            $this->flasher->success('User deleted');
        } else {
            $this->flasher->error('User not deleted.');
            if ($this->site['debug']) {
                $this->flasher->error(Gatekeeper::getLastError());
            }
        }
        $this->redirect('/users');
    }

    public function deleteGroupAction(int $id)
    {
        if (Gatekeeper::deleteGroupById($id)) {
            $this->flasher->success('Group deleted');
        } else {
            $this->flasher->error('Group not deleted.');
            if ($this->site['debug']) {
                $this->flasher->error(Gatekeeper::getLastError());
            }
        }
        $this->redirect('/users/groups');
    }
}
