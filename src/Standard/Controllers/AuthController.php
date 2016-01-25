<?php

namespace Standard\Controllers;

use GuzzleHttp\ClientInterface;
use Twig_Environment;

class AuthController
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index()
    {
        echo $this->twig->render('auth/index.twig');
    }

    public function processSignup()
    {
        echo $this->twig->render('auth/signup.twig');
    }

    public function processLogin()
    {
        echo $this->twig->render('auth/login.twig');
    }


    /**
     * @Inject({"GuzzleHttp\ClientInterface", "mailgun-config", "site-config"})
     * @param ClientInterface $client
     * @param array $mailgun
     * @param array $site
     */
    public function forgotPassword(ClientInterface $client, array $mailgun, array $site) {

        $html = $this->twig->render('emails/forgotpass.twig', ['code' => 'foo']);
        $url = 'https://api.mailgun.net/v3/'.$mailgun['domain'].'/messages';

        $replyto = $site['replyto'] ?? $site['sender'];

//        $response = $client->request('POST', $url, [
//            'auth' => ['api', $mailgun['key']],
//            'multipart' => [
//                ['name' => 'to', 'contents' => 'bruno@skvorc.me'],
//                ['name' => 'from', 'contents' => $replyto],
//                ['name' => 'subject', 'contents' => 'Forgot your password?'],
//                ['name' => 'html', 'contents' => $html]
//            ]
//        ]);
//
//        dump($response);

//        switch ($_SERVER['REQUEST_METHOD']) {
//            case "GET":
//
//                break;
//            case "POST":
//                $html = $this->twig->render('auth/forgotpass.twig',
//                    ['method' => $_SERVER['REQUEST_METHOD']]);
//                break;
//            default:
//                echo $this->twig->render('error405.twig');
//        }
//
//


    }
}
