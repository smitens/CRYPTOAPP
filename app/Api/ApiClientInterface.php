<?php

namespace CryptoApp\Api;

use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Models\Currency;

interface ApiClientInterface
{
    public function getTopCryptoCurrencies(int $limit = 10): array;

    /**
     * @throws HttpFailedRequestException
     */
    public function searchCryptoCurrencies(string $symbol): Currency;
}
