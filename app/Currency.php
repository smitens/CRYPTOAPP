<?php
namespace CryptoApp\App;

class Currency
{
    private ?int $rank;
    private string $name;
    private string $symbol;
    private float $price;

    public function __construct(?int $rank = null, string $name, string $symbol, float $price)
    {
        $this->rank = $rank;
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}