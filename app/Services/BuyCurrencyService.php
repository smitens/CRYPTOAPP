<?php

namespace CryptoApp\Services;

use CryptoApp\Repositories\Currency\CurrencyRepository;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Exceptions\InsufficientBalanceException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Models\Transaction;
use CryptoApp\Models\Wallet;
use Carbon\Carbon;
use CryptoApp\Repositories\User\UserRepository;
use CryptoApp\Repositories\Wallet\WalletRepository;
use Exception;

class BuyCurrencyService
{

    private CurrencyRepository $currencyRepository;
    private TransactionRepository $transactionRepository;
    private WalletRepository $walletRepository;
    private UserRepository $userRepository;
    private string $userId;

    public function __construct(
        CurrencyRepository $currencyRepository,
        TransactionRepository  $transactionRepository,
        WalletRepository $walletRepository,
        UserRepository $userRepository,
        string $userId
    )
    {
        $this->currencyRepository = $currencyRepository;
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
        $this->userId = $userId;
    }

    public function execute(string $symbol, float $amount): void
    {
        try {
            $user = $this->userRepository->getById($this->userId);
            if (!$user) {
                throw new UserNotFoundException("User with ID {$this->userId} not found.");
            }

            $currency = $this->currencyRepository->search($symbol);
            $totalCost = $currency->getPrice() * $amount;
            $wallet = $this->walletRepository->get($this->userId);
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

            $this->transactionRepository->save($transaction);

            $newBalance = $currentBalance - ($currency->getPrice() * $amount);
            $updatedWallet = new Wallet($this->userId, $newBalance);
            $this->walletRepository->update($updatedWallet);

            echo "Successfully bought $amount $symbol.\n";

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientBalanceException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "An unexpected error occurred: " . $e->getMessage();
        }
    }
}