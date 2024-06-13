<?php

namespace CryptoApp\App;

interface ApiClientInterface
{
    public function getTopCryptoCurrencies(int $limit = 10): array;

    public function searchCryptoCurrencies(string $symbol): Currency;
}
