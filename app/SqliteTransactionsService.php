<?php
namespace CryptoApp\App;
require 'vendor/autoload.php';

use Medoo\Medoo;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class SqliteTransactionsService implements DataServiceInterface
{
    private Medoo $database;
    private ?ApiClientInterface $api;

    public function __construct(string $databaseFile = 'storage/database.sqlite', ?ApiClientInterface $api = null)
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $databaseFile,
        ]);
        $this->api = $api;
        $this->createTable();
    }

    private function createTable(): void
    {
        try {
            $this->database->exec('CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL,
            symbol TEXT NOT NULL,
            amount REAL NOT NULL,
            price REAL NOT NULL,
            timestamp INTEGER NOT NULL
            )');
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function addTransaction(Transaction $transaction): void
    {
        try{
            $this->database->insert('transactions', [
            'type' => $transaction->getType(),
            'symbol' => strtoupper($transaction->getSymbol()),
            'amount' => $transaction->getAmount(),
            'price' => $transaction->getPrice(),
            'timestamp' => $transaction->getTimestamp(),
                ]);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
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
        $transactions = [];

        try {
            $columns = ['id', 'type', 'symbol', 'amount', 'price', 'timestamp'];
            $transactionsData = $this->database->select('transactions', $columns);

            foreach ($transactionsData as $data) {
                $transactions[] = Transaction::fromArray($data);
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        return $transactions;
    }

    public function displayTransactions(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp']);

        foreach ($this->getTransactions() as $transaction) {
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