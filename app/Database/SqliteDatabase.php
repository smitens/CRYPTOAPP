<?php

namespace CryptoApp\Database;

use CryptoApp\Exceptions\DatabaseException;
use CryptoApp\Exceptions\TransactionGetException;
use CryptoApp\Exceptions\TransactionSaveException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Exceptions\WalletUpdateException;
use CryptoApp\Models\Transaction;
use CryptoApp\Models\User;
use CryptoApp\Models\Wallet;
use Medoo\Medoo;
use Exception;

class SqliteDatabase implements DatabaseInterface
{
    private Medoo $database;

    public function __construct(string $databaseFile = 'storage/database.sqlite')
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $databaseFile,
        ]);

        $this->createTables();
    }


    private function createTables(): void
    {
        try {
            $this->database->exec('CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL
            )');

            $this->database->exec('CREATE TABLE IF NOT EXISTS wallets (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                balance REAL NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users (id)
            )');

            $this->database->exec('CREATE TABLE IF NOT EXISTS transactions (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                type TEXT NOT NULL,
                symbol TEXT NOT NULL,
                amount REAL NOT NULL,
                price REAL NOT NULL,
                timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users (id)
            )');

        } catch (Exception $e) {
            throw new DatabaseException("Error creating tables: " . $e->getMessage());

        }
    }

    public function saveTransaction (Transaction $transaction): void
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

    public function getTransactions(): array
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

    public function getTransactionsByUserId(string $userId): array
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

    public function saveWallet(Wallet $wallet): void
    {
        try {
            $this->database->insert('wallets', [
                'id' => $wallet->getId(),
                'user_id' => $wallet->getUserId(),
                'balance' => $wallet->getBalance(),
            ]);
        } catch (Exception $e) {
            throw new UserSaveException("Failed to save wallet: " . $e->getMessage());
        }
    }

    public function getWallet(string $userId): ?array
    {
        try {
            $walletData = $this->database->get('wallets', '*', ['user_id' => $userId]);
            return $walletData ?? null;
        } catch (Exception $e) {
            throw new WalletNotFoundException("Wallet no found: " . $e->getMessage());
        }
    }

    public function updateWallet(Wallet $wallet): void
    {
        try {
            $this->database->update('wallets', [
                'balance' => $wallet->getBalance(),
            ], [
                'id' => $wallet->getId(),
            ]);
        } catch (Exception $e) {
            throw new WalletUpdateException("Failed to update wallet: " . $e->getMessage());
        }
    }

    public function saveUser(User $user): void
    {
        try {
            $this->database->insert('users', [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
            ]);
        } catch (Exception $e) {
            throw new UserSaveException("Failed to save user: " . $e->getMessage());
        }
    }

    public function getUserById(string $userId): ?array
    {
        try {
            $userData = $this->database->get('users', '*', ['id' => $userId]);
            return $userData ?? null;
        } catch (Exception $e) {
            throw new UserNotFoundException("User no found: " . $e->getMessage());
        }
    }

    public function getUserByUsernameAndPassword (string $username, string $password): ?array {
        $hashedPassword = md5($password);

            $userData = $this->database->get('users', '*', [
                'username' => $username,
                'password' => $hashedPassword,
            ]);

            if (!$userData) {
                throw new UserNotFoundException("User doesn't exist or incorrect password!");
            }
            return $userData;
    }

}