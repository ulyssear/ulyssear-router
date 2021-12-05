<?php
declare(strict_types=1);

namespace Ulyssear;

class Route
{

    private $callback;
    private string $uri;
    private ?string $name;
    private string $method;
    private Collection $data;


    public function __construct(string $method, string $uri, $callback, ?string $name, array $data = []) {
        $this->name = $name;
        $this->uri = $uri;
        $this->method = $method;
        $this->callback = $callback;
        $this->data = new Collection($data);
    }

    public function uri() {
        return $this->uri;
    }

    public function name() {
        return $this->name;
    }

    public function callback() {
        return $this->callback;
    }

    public function data() {
        return $this->data;
    }

    public function method() {
        return $this->method;
    }

}