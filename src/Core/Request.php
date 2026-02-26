<?php

namespace Diablo\Core;

class Request {
    private array $get;
    private array $post;
    private array $server;
    private string $rawBody;

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->rawBody = file_get_contents('php://input');
    }

    public function get(string $key, $default = null) {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null) {
        return $this->post[$key] ?? $default;
    }

    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getPost(): array {
        return $_POST;
    }

    public function getJson(): array {
        $decoded = json_decode($this->rawBody, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getHeader(string $name): ?string {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$headerKey] ?? null;
    }
}