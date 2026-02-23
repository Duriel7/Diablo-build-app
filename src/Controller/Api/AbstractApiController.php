<?php

namespace Diablo\Controller\Api;

use Diablo\Controller\AbstractController;
use Diablo\Core\Request;

abstract class AbstractApiController extends AbstractController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function success(array $data = [], int $status = 200): void
    {
        $this->json([
            'success' => true,
            'data' => $data
        ], $status);
    }

    protected function error(string $message, int $status = 400): void
    {
        $this->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}