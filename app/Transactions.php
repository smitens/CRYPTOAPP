<?php
namespace CryptoApp\App;
require 'vendor/autoload.php';

use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class Transactions
{
    private string $transactionsFile;
    private array $transactions;
    private ?ApiToData $api;

    public function __construct(string $transactionsFile = 'transactions.json', ?ApiToData $api = null) {
        $this->transactionsFile = $transactionsFile;
        $this->api = $api;
        $this->transactions = $this->loadTransactions();
    }

    private function loadTransactions(): array
    {
        if (file_exists($this->transactionsFile)) {
            $jsonData = file_get_contents($this->transactionsFile);
            $transactions = json_decode($jsonData, true);

            if (is_array($transactions)) {
                return $transactions;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    private function saveTransactions(): void
    {
        file_put_contents($this->transactionsFile, json_encode($this->transactions, JSON_PRETTY_PRINT));
    }

    public function addTransaction(array $transaction): void
    {
        $this->transactions[] = $transaction;
        $this->saveTransactions();
    }

    public function buy(string $symbol, float $amount): void
    {
        try {
            $cryptoData = $this->api->searchCryptoCurrencies($symbol);
            $price = $cryptoData[$symbol]['quote']['USD']['price'];

            $transaction = [
                'type' => 'buy',
                'symbol' => $symbol,
                'amount' => $amount,
                'price' => $price,
                'timestamp' => time(),
            ];
            $this->addTransaction($transaction);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function sell(string $symbol, float $amount): void
    {
        try {
            $cryptoData = $this->api->searchCryptoCurrencies($symbol);
            $price = $cryptoData[$symbol]['quote']['USD']['price'];

            $transaction = [
                'type' => 'sell',
                'symbol' => $symbol,
                'amount' => $amount,
                'price' => $price,
                'timestamp' => time(),
            ];
            $this->addTransaction($transaction);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function displayTransactions(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp']);

        foreach ($this->transactions as $transaction) {
            $table->addRow([
                $transaction['type'],
                $transaction['symbol'],
                $transaction['amount'],
                number_format($transaction['price'], 8),
                Carbon::createFromTimestamp($transaction['timestamp'])->format('Y-m-d H:i:s'),
            ]);
        }
        $table->render();
    }
}