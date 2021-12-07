<?php
declare(strict_types=1);

namespace Ulyssear;

class Response
{

    private int $status;
    private string $uri;
    private Collection $headers;
    private array|string $content;

    public function __construct(int $status, ?string $uri = null, array $headers = [], $content = '') {
        $this->headers = new Collection($headers);
        $this->uri = $uri ?? $_SERVER['REQUEST_URI'];
        $this->status = $status;
        $this->content = $content;
    }

    public function body() {
        $this->headers->pushNamedItem('Content-Type', 'text/html');

        return true;
    }

    public function json(array|string $content = null) {
        $this->headers->pushNamedItem('Content-Type', 'text/json');

        $this->writeHeader();

        $content ??= $this->content;

        if (gettype($content) === 'array') {
            return json_encode($content);
        }

        return $content;
    }

    public function view (string $name) {
        $this->headers->pushNamedItem('Content-Type', 'text/html');

        $this->writeHeader();

        $this->content = include_once Template::make($name);

        return $this->content;
    }

    private function writeHeader() {
        foreach($this->headers->entries() as $name => $value) {
            header("$name:$value");
        }
    }

}