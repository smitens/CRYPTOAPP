<?php

namespace CryptoApp\Services;

use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Models\Wallet;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Repositories\Wallet\WalletRepository;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class WalletService
{
    public const INITIAL_BALANCE = 1000.0;

    private Wallet $wallet;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;
    private CurrencyRepository $currencyRepository;
    private string $userId;

    public function __construct(
        Wallet $wallet,
        WalletRepository $walletRepository,
        TransactionRepository $transactionRepository,
        CurrencyRepository $currencyRepository,
        string $userId
    )
    {
        $this->wallet = $wallet;
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
        $this->currencyRepository = $currencyRepository;
        $this->userId = $userId;
    }

    public function createWallet(): void
    {
        $initialBalance = self::INITIAL_BALANCE;

        $newWallet = new Wallet($this->userId, $initialBalance);

        $this->walletRepository->save($newWallet);
    }

    public function calculateWalletState(): array
    {
        $state = [];
        $wallet = $this->walletRepository->get($this->userId);

        if (!$wallet) {
            throw new WalletNotFoundException("Wallet not found for user ID: " . $this->userId);
        }

        $balance = $this->wallet->getBalance();
        $transactions = $this->transactionRepository->getByUserId($this->userId);

        foreach ($transactions as $transaction) {
            $symbol = strtoupper($transaction->getSymbol());
            $amount = $transaction->getAmount();
            $total = $transaction->getPrice() * $amount;

            if ($transaction->getType() === 'buy') {
                $balance -= $total;
                if (!isset($state[$symbol])) {
                    $state[$symbol] = ['amount' => 0, 'totalSpent' => 0];
                }
                $state[$symbol]['amount'] += $amount;
                $state[$symbol]['totalSpent'] += $total;
            } elseif ($transaction->getType() === 'sell') {
                $balance += $total;
                if (!isset($state[$symbol])) {
                    $state[$symbol] = ['amount' => 0, 'totalSpent' => 0];
                }
                $state[$symbol]['amount'] -= $amount;
                $state[$symbol]['totalSpent'] -= $total;
            }
        }

        foreach ($state as $symbol => &$data) {
            if ($symbol !== 'balance') {
                $amount = $data['amount'];
                $totalSpent = $data['totalSpent'];
                $avgPurchasePrice = $totalSpent / $amount;
                $currentPrice = $this->currencyRepository->search($symbol)->getPrice();
                $profitLoss = (($currentPrice - $avgPurchasePrice) / $avgPurchasePrice) * 100;

                $data['currentPrice'] = $currentPrice;
                $data['profitLoss'] = $profitLoss;
            }
        }

        $state['balance'] = $balance;
        return $state;
    }

    public function getBalance(): float
    {
        $state = $this->calculateWalletState();
        return $state['balance'];
    }

    public function getExistingAmountInWallet(string $symbol): float
    {
        $transactions = $this->transactionRepository->getByUserId($this->userId);
        $existingAmount = 0.0;

        foreach ($transactions as $transaction) {
            if ($transaction->getSymbol() === $symbol && $transaction->getType() === 'buy') {
                $existingAmount += $transaction->getAmount();
            } elseif ($transaction->getSymbol() === $symbol && $transaction->getType() === 'sell') {
                $existingAmount -= $transaction->getAmount();
            }
        }

        return $existingAmount;
    }

    public function displayWalletState(): void
    {
        $state = $this->calculateWalletState();
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Symbol', 'Amount', 'Avg Purchase Price', 'Current Price', 'Profit/Loss (%)']);

        foreach ($state as $symbol => $data) {
            if ($symbol !== 'balance') {
                $amount = $data['amount'];
                $totalSpent = $data['totalSpent'];
                $avgPurchasePrice = $totalSpent / $amount;
                $currentPrice = $data['currentPrice'];
                $profitLoss = $data['profitLoss'];

                $table->addRow([
                    $symbol,
                    $amount,
                    number_format($avgPurchasePrice, 8),
                    number_format($currentPrice, 8),
                    number_format($profitLoss, 2) . "%",
                ]);
            }
        }

        $table->render();
        $output->writeln("Balance: $" . number_format($state['balance'], 2));
    }
}