<?php

namespace CryptoApp;

use CryptoApp\Database\DatabaseInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class Wallet
{
    private float $balance;
    private DatabaseInterface $database;

    public function __construct(
        float $initialBalance,
        DatabaseInterface $database
    )
    {
        $this->balance = $initialBalance;
        $this->database = $database;
    }

    public function calculateWalletState(): array
    {
        $state = [];
        $balance = $this->balance;

        foreach ($this->database->getAll() as $transaction) {
            $symbol = strtoupper($transaction->getSymbol());
            $amount = $transaction->getAmount();
            $total = $transaction->getPrice() * $amount;

            if ($transaction->getType() === 'buy') {
                $balance -= $total;
                if (!isset($state[$symbol])) {
                    $state[$symbol] = 0;
                }
                $state[$symbol] += $amount;
            } elseif ($transaction->getType() === 'sell') {
                $balance += $total;
                if (!isset($state[$symbol])) {
                    $state[$symbol] = 0;
                }
                $state[$symbol] -= $amount;
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

    public function displayWalletState(): void
    {
        $state = $this->calculateWalletState();
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Symbol', 'Amount']);

        foreach ($state as $symbol => $amount) {
            if ($symbol !== 'balance') {
                $table->addRow([$symbol, $amount]);
            }
        }

        $table->render();
        $output->writeln("Balance: $" . number_format($state['balance'], 2));
        echo "\n";
    }
}