<?php
namespace Diablo\Controller\Admin;

use Diablo\Controller\Web\AbstractWebController;
use Diablo\Core\Request;

abstract class AbstractAdminController extends AbstractWebController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'admin') {
            $this->redirect('/login');
        }
    }
}