<?php
namespace CryptoApp\App;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class Wallet
{
    private float $balance;
    private Transactions $transactions;

    public function __construct(float $initialBalance, Transactions $transactions)
    {
        $this->balance = $initialBalance;
        $this->transactions = $transactions;
    }

    public function calculateWalletState(): array
    {
        $state = [];
        $balance = $this->balance;

        foreach ($this->transactions->getTransactions() as $transaction) {
            $symbol = $transaction['symbol'];
            $amount = $transaction['amount'];
            $total = $transaction['price'] * $amount;

            if ($transaction['type'] === 'buy') {
                $balance -= $total;
                if (!isset($state[$symbol])) {
                    $state[$symbol] = 0;
                }
                $state[$symbol] += $amount;
            } elseif ($transaction['type'] === 'sell') {
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