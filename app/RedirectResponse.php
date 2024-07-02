<?php

namespace CryptoApp;

class RedirectResponse
{
    private string $location;
    private ?string $errorMessage;

    public function __construct(string $location, ?string $errorMessage = null)
    {
        $this->location = $location;
        $this->errorMessage = $errorMessage;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
