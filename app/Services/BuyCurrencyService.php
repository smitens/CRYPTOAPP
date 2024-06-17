<?php

namespace CryptoApp\Services;

use CryptoApp\Api\ApiClientInterface;
use CryptoApp\Database\DatabaseInterface;
use CryptoApp\Exceptions\InsufficientBalanceException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Models\Transaction;
use CryptoApp\Models\Wallet;
use Carbon\Carbon;
use Exception;

class BuyCurrencyService
{

    private ApiClientInterface $apiClient;
    private DatabaseInterface $database;
    private string $userId;

    public function __construct(
        ApiClientInterface $apiClient,
        DatabaseInterface  $database,
        string             $userId
    )
    {
        $this->apiClient = $apiClient;
        $this->database = $database;
        $this->userId = $userId;
    }

    public function execute(string $symbol, float $amount): void
    {
        try {
            $user = $this->database->getUserById($this->userId);
            if (!$user) {
                throw new UserNotFoundException("User with ID {$this->userId} not found.");
            }

            $currency = $this->apiClient->searchCryptoCurrencies($symbol);
            $totalCost = $currency->getPrice() * $amount;
            $wallet = $this->database->getWallet($this->userId);
            if (!$wallet) {
                throw new WalletNotFoundException("User's wallet not found.");
            }
            if ($wallet['balance'] < $totalCost) {
                throw new InsufficientBalanceException(
                    "Insufficient balance to buy $symbol. Required: $totalCost"
                );
            }
            $currentBalance = $wallet['balance'];

            $timestamp = Carbon::now();
            $transaction = new Transaction(
                $this->userId,
                'buy',
                $symbol,
                $amount,
                $currency->getPrice(),
                $timestamp,
            );

            $this->database->saveTransaction($transaction);

            $newBalance = $currentBalance - ($currency->getPrice() * $amount);
            $updatedWallet = new Wallet($this->userId, $newBalance);
            $this->database->updateWallet($updatedWallet);

            echo "Successfully bought $amount $symbol.\n";

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientBalanceException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "An unexpected error occurred: " . $e->getMessage();
        }
    }
}