<?php

namespace CryptoApp\Database;

use CryptoApp\Models\Transaction;
use Medoo\Medoo;
use Exception;

class SqliteDatabase implements DatabaseInterface
{
    private Medoo $database;

    public function __construct(
        string $databaseFile = 'storage/database.sqlite'
    )
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $databaseFile,
        ]);
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
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function save (Transaction $transaction): void
    {
        try{
            $this->database->insert('transactions', [
                'type' => $transaction->getType(),
                'symbol' => strtoupper($transaction->getSymbol()),
                'amount' => $transaction->getAmount(),
                'price' => $transaction->getPrice(),
                'timestamp' => $transaction->getTimestamp(),
            ]);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getAll(): array
    {
        $transactions = [];

        try {
            $transactionsData = $this->database->select('transactions',
                ['id', 'type', 'symbol', 'amount', 'price', 'timestamp']
            );

            foreach ($transactionsData as $data) {
                $transactions[] = Transaction::fromArray($data);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        return $transactions;
    }

}