<?php

namespace CryptoApp\Repositories\Wallet;

use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Exceptions\WalletUpdateException;
use CryptoApp\Models\Wallet;
use Exception;
use Medoo\Medoo;

class SqliteWalletRepository implements WalletRepository
{
    private Medoo $database;

    public function __construct(string $databaseFile = 'storage/database.sqlite')
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $databaseFile,
        ]);
    }

    public function save(Wallet $wallet): void
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

    public function get(string $userId): ?array
    {
        try {
            $walletData = $this->database->get('wallets', '*', ['user_id' => $userId]);
            return $walletData ?? null;
        } catch (Exception $e) {
            throw new WalletNotFoundException("Wallet no found: " . $e->getMessage());
        }
    }

    public function update(Wallet $wallet): void
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
}