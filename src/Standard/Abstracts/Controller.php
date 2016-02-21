<?php

namespace Standard\Abstracts;

abstract class Controller
{
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