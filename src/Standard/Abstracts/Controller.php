<?php

namespace Standard\Abstracts;

use Psr\Log\LoggerInterface;
use Tamtamchik\SimpleFlash\Flash;

abstract class Controller
{
    /**
     * @Inject
     * @var Flash
     */
    protected $flasher;


    /**
     * @Inject("site-config")
     * @var array
     */
    protected $site;

    /**
     * @Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Redirects the app to a given URL, absolute or relative, remote or local.
     *
     * @param string $url
     * @return void
     */
    protected function redirect(string $url)
    {
        header('Location: '.$url);
        die();
    }
}