<?php
namespace Diablo\Controller\Api;

use Diablo\Controller\MasterController;
use Diablo\Http\Response;
abstract class ApiController extends MasterController {
    // Constructor
    public function __construct(){
        header("Content-type: application/json; charset=utf-8");
    }
    
    // Home method for routes
    public function home(): void {
        Response::success([
            'route' => [
                'GET /members',
                'GET /posts',
                'POST /register',
                'POST /login',
            ],
        ]);
    }

    
}