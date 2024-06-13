<?php
namespace CryptoApp\App;

interface DataServiceInterface
{
    public function addTransaction(Transaction $transaction): void;
    public function getTransactions(): array;
    public function displayTransactions(): void;
    public function buy(string $symbol, float $amount): void;
    public function sell(string $symbol, float $amount): void;
}

