<?php

namespace CryptoApp\Services;

use Medoo\Medoo;
use CryptoApp\Exceptions\DatabaseException;
use Exception;

class DatabaseInitializer
{
    private string $databaseFile;

    public function __construct(string $databaseFile = 'storage/database.sqlite')
    {
        $this->databaseFile = $databaseFile;
        $this->initializeDatabase();
    }

    private function initializeDatabase(): void
    {
        if (!file_exists($this->databaseFile)) {
            touch($this->databaseFile);
            $this->createTables();
        }
    }

    private function createTables(): void
    {
        $database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $this->databaseFile,
        ]);

        try {
            $database->exec('CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL
            )');

            $database->exec('CREATE TABLE IF NOT EXISTS wallets (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                balance REAL NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users (id)
            )');

            $database->exec('CREATE TABLE IF NOT EXISTS transactions (
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
}