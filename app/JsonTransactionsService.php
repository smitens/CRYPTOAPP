<?php
namespace CryptoApp\App;
require 'vendor/autoload.php';

use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class JsonTransactionsService implements DataServiceInterface
{
    private string $transactionsFile;
    private array $transactions;
    private ?ApiClientInterface $api;

    public function __construct(string $transactionsFile = 'transactions.json', ?ApiClientInterface $api = null) {
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
                return array_map(function ($transactionData) {
                    return Transaction::fromArray($transactionData);
                }, $transactions);
            }
        }
        return [];
    }

    private function saveTransactions(): void
    {
        $serializedTransactions = array_map(function ($transaction) {
            return $transaction->jsonSerialize();
        }, $this->transactions);

        file_put_contents($this->transactionsFile, json_encode($serializedTransactions, JSON_PRETTY_PRINT));
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
        $this->saveTransactions();
    }

    public function buy(string $symbol, float $amount): void
    {
        try {
            $cryptoData = $this->api->searchCryptoCurrencies($symbol);
            $price = $cryptoData->getPrice();
            $transaction = new Transaction('buy', $symbol, $amount, $price, time());
            $this->addTransaction($transaction);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function sell(string $symbol, float $amount): void
    {
        try {
            $cryptoData = $this->api->searchCryptoCurrencies($symbol);
            $price = $cryptoData->getPrice();
            $transaction = new Transaction('sell', $symbol, $amount, $price, time());
            $this->addTransaction($transaction);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getApi(): ?ApiClientInterface
    {
        return $this->api;
    }

    public function displayTransactions(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp']);

        foreach ($this->transactions as $transaction) {
            $table->addRow([
                $transaction->getType(),
                $transaction->getSymbol(),
                $transaction->getAmount(),
                number_format($transaction->getPrice(), 8),
                Carbon::createFromTimestamp($transaction->getTimestamp())->format('Y-m-d H:i:s'),
            ]);
        }
        $table->render();
    }
}