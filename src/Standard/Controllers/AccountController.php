<?php

namespace Standard\Controllers;

use Twig_Environment;

/**
 * Class AccountController
 * @package Standard\Controllers
 *
 * @auth-groups admin
 */
class AccountController
{

    /**
     * @var Twig_Environment
     */
    private $twig;

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
            'message' => 'Hello from Account, invoked',
        ]);
    }

    /**
     * @auth-groups reg-user
     */
    public function indexAction()
    {
        echo $this->twig->render('account/index.twig', [
            'message' => 'Hello from account, index action',
        ]);
    }
}
