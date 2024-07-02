<?php

namespace CryptoApp;

class Response
{
    private string $template;
    private array $data;

    public function __construct
    (
        string $template,
        array $data
    )
    {
        $this->template = $template;
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
