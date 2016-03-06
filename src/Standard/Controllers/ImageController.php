<?php

namespace Standard\Controllers;

use League\Glide\Server;
use Standard\Abstracts\Controller;
use Twig_Environment;

class ImageController extends Controller
{
    /**
     * @Inject("glide")
     * @var Server
     */
    private $glide;

    private $allowedWidths = [
        320,
        460,
        768,
        992,
        1280,
        1920,
    ];

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderImage($image)
    {
        $width = $this->getWidth($image);

        $imageSource = str_replace('-'.$width, '', $image);

        $imagePath = $this->glide->makeImage($imageSource, ['w' => $width]);
        $imageBase = $this->glide->getCache()->read($imagePath);
        $this->glide->getCache()->put($image, $imageBase);
        $this->glide->outputImage($imageSource, ['w' => $width]);
    }

    /**
     * @param string $imagePath
     * @return int
     */
    private function getWidth(string $imagePath) : int
    {
        $fragments = explode('-', $imagePath);

        if (count($fragments) < 2) {
            header(
                ($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 500 ' . 'Server Error ImgErr00'
            );
            die("Nope! Image fragments missing.");
        }

        $width = (int)explode('.', $fragments[1])[0];
        if (!in_array($width, $this->allowedWidths)) {
            header(
                ($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 500 ' . 'Server Error ImgErr01'
            );
            die("Nope! Disallowed width: ".$width." for image ".$imagePath);
        }

        return $width;
    }

    public function demo()
    {
        echo $this->twig->render('image/demo.twig');
    }
}