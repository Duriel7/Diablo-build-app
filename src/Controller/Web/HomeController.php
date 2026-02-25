<?php

namespace Diablo\Controller\Web;

class HomeController extends AbstractWebController
{
    public function index(): void
    {
        $this->render('home/index.html.twig');
    }
}