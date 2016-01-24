<?php

namespace Standard\Controllers;

use Twig_Environment;

class ExtraController
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
        echo $this->twig->render('home.twig', [
            'message' => 'Hello from Extra, invoked',
        ]);
    }

    public function indexAction()
    {
        echo $this->twig->render('extra/index.twig', [
            'message' => 'Hello from Extra, index action',
        ]);
    }
}
