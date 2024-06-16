<?php

namespace CryptoApp\Services;

use CryptoApp\Api\ApiClientInterface;
use CryptoApp\Database\DatabaseInterface;
use CryptoApp\Models\Transaction;
use Exception;

class SellCurrencyService {

    private ApiClientInterface $apiClient;
    private DatabaseInterface $database;

    public function __construct(
        ApiClientInterface $apiClient,
        DatabaseInterface $database
    )
    {
        $this->apiClient = $apiClient;
        $this->database = $database;
    }

    public function execute (string $symbol, float $amount): void
    {
        try {
            $currency = $this->apiClient->searchCryptoCurrencies($symbol);
            $transaction = new Transaction(
                'sell',
                $symbol,
                $amount,
                $currency->getPrice(),
                time());
            $this->database->save($transaction);

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

    }


}