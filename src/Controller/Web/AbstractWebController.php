<?php

namespace Diablo\Controller\Web;

use Diablo\Controller\AbstractController;
use Diablo\Core\Request;

abstract class AbstractWebController extends AbstractController
{
    protected \Twig\Environment $twig;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $loader = new \Twig\Loader\FilesystemLoader(
            $_SERVER['DOCUMENT_ROOT'] . '/../src/View'
        );

        $this->twig = new \Twig\Environment($loader, [
            'cache' => $_SERVER['DOCUMENT_ROOT'] . '/../var/cache',
            'debug' => true
        ]);

        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

        $this->twig->addFunction(
            new \Twig\TwigFunction('file_exists', fn($file) => file_exists($file))
        );

        $this->twig->addGlobal('session', $_SESSION);
    }

    protected function render(string $template, array $params = []): void
    {
        echo $this->twig->render($template, $params);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}