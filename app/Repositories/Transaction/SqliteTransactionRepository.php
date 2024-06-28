<?php

namespace CryptoApp\Repositories\Transaction;

use CryptoApp\Exceptions\TransactionGetException;
use CryptoApp\Exceptions\TransactionSaveException;
use CryptoApp\Models\Transaction;
use Exception;
use Medoo\Medoo;

class SqliteTransactionRepository implements TransactionRepository
{
    private Medoo $database;

    public function __construct(string $databaseFile = 'storage/database.sqlite')
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $databaseFile,
        ]);
    }

    public function save(Transaction $transaction): void
    {
        try{
            $this->database->insert('transactions', [
                'id' => $transaction->getId(),
                'user_id' => $transaction->getUserId(),
                'type' => $transaction->getType(),
                'symbol' => strtoupper($transaction->getSymbol()),
                'amount' => $transaction->getAmount(),
                'price' => $transaction->getPrice(),
                'timestamp' => $transaction->getTimestamp()->format('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            throw new TransactionSaveException("Failed to save transaction: " . $e->getMessage());
        }
    }

    public function get(): array
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
            throw new TransactionGetException("Failed to get transactions: " . $e->getMessage());
        }

        return $transactions;
    }

    public function getByUserId(string $userId): array
    {
        $transactions = [];

        try {
            $transactionsData = $this->database->select('transactions',
                ['id', 'user_id', 'type', 'symbol', 'amount', 'price', 'timestamp'],
                ['user_id' => $userId]
            );

            foreach ($transactionsData as $data) {
                $transactions[] = Transaction::fromArray($data);
            }
        } catch (Exception $e) {
            throw new TransactionGetException("Failed to get transactions: " . $e->getMessage());
        }

        return $transactions;
    }
}