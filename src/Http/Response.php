<?php

namespace Diablo\Http;

class Response
{
    /**
     * Send a success JSON response
     *
     * @param mixed $data
     * @param int $status
     */
    public static function success(mixed $data = null, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Send an error JSON response
     *
     * @param string $message
     * @param int $status
     */
    public static function error(string $message, int $status = 400): void
    {
        http_response_code($status);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}