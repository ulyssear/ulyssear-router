<?php
declare(strict_types=1);

namespace Ulyssear;

require_once __DIR__ . '/../vendor/autoload.php';

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

    }

    public function json(array|string $content = null) {
        $this->headers->pushNamedItem('Content-Type', 'text/json');

        foreach($this->headers->entries() as $name => $value) {
            header("$name:$value");
        }

        $content ??= $this->content;

        if (gettype($content) === 'array') {
            return json_encode($content);
        }

        return $content;
    }

}