<?php

namespace CryptoApp\Repositories\Currency;

use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Exceptions\NoSuchCurrencyException;
use CryptoApp\Models\Currency;


interface CurrencyRepository
{
    public function getTop(int $limit = 10): array;

    /**
     * @throws HttpFailedRequestException
     * @throws NoSuchCurrencyException
     */
    public function search(string $symbol): Currency;

}