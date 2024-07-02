<?php

namespace CryptoApp\Services;

use CryptoApp\Repositories\Currency\CurrencyRepository;
use CryptoApp\Repositories\User\UserRepository;
use CryptoApp\Repositories\Wallet\WalletRepository;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Exceptions\InsufficientCryptoAmountException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Models\Transaction;
use CryptoApp\Models\Wallet;
use Carbon\Carbon;
use Exception;

class SellCurrencyService {

    private CurrencyRepository $currencyRepository;
    private TransactionRepository $transactionRepository;
    private WalletRepository $walletRepository;
    private UserRepository $userRepository;
    private WalletService $walletService;
    private string $userId;

    public function __construct(
        CurrencyRepository $currencyRepository,
        TransactionRepository  $transactionRepository,
        WalletRepository $walletRepository,
        UserRepository $userRepository,
        WalletService $walletService,
        string $userId
    )
    {
        $this->currencyRepository = $currencyRepository;
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
        $this->walletService = $walletService;
        $this->userId = $userId;
    }

    public function execute (string $symbol, float $amount): void
    {
        try {
            $user = $this->userRepository->getById($this->userId);
            if (!$user) {
                throw new UserNotFoundException("User with ID {$this->userId} not found.");
            }

            $currency = $this->currencyRepository->search($symbol);
            $wallet = $this->walletRepository->get($this->userId);
            if (!$wallet) {
                throw new WalletNotFoundException("User's wallet not found.");
            }
            $currentBalance = $wallet['balance'];
            $existingAmount = $this->walletService->getExistingAmountInWallet($symbol);

            if ($existingAmount < $amount) {
                throw new InsufficientCryptoAmountException("Not enough $symbol to sell.");
            }
            $timestamp = Carbon::now();
            $transaction = new Transaction(
                $this->userId,
                'sell',
                $symbol,
                $amount,
                $currency->getPrice(),
                $timestamp,
            );
            $this->transactionRepository->save($transaction);

            $newBalance = $currentBalance + ($currency->getPrice() * $amount);
            $updatedWallet = new Wallet($this->userId, $newBalance);
            $this->walletRepository->update($updatedWallet);

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientCryptoAmountException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("An unexpected error occurred: " . $e->getMessage());
        }
    }
}