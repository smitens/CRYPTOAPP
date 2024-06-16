<?php

namespace CryptoApp\Models;

use JsonSerializable;

class Transaction implements JsonSerializable
{
    private string $type;
    private string $symbol;
    private float $amount;
    private float $price;
    private int $timestamp;

    public function __construct(
        string $type,
        string $symbol,
        float $amount,
        float $price,
        int $timestamp
    )
    {
        $this->type = $type;
        $this->symbol = strtoupper($symbol);
        $this->amount = $amount;
        $this->price = $price;
        $this->timestamp = $timestamp;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'symbol' => $this->symbol,
            'amount' => $this->amount,
            'price' => $this->price,
            'timestamp' => $this->timestamp,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'],
            $data['symbol'],
            $data['amount'],
            $data['price'],
            $data['timestamp']
        );
    }
}