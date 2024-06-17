<?php

namespace CryptoApp\Database;

use CryptoApp\Exceptions\DatabaseException;
use CryptoApp\Exceptions\TransactionSaveException;
use CryptoApp\Models\Transaction;
use CryptoApp\Models\User;
use CryptoApp\Models\Wallet;

interface DatabaseInterface
{
    /**
     * @throws DatabaseException
     * @throws TransactionSaveException
     */
    public function saveTransaction (Transaction $transaction): void;
    public function getTransactions (): array;
    public function getTransactionsByUserId(string $userId): array;
    public function saveWallet(Wallet $wallet): void;
    public function getWallet (string $userId): ?array;
    public function saveUser(User $user): void;
    public function getUserById (string $userId): ?array;
}
