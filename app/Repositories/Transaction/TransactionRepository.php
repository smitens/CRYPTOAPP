<?php

namespace CryptoApp\Repositories\Transaction;

use CryptoApp\Exceptions\DatabaseException;
use CryptoApp\Exceptions\TransactionSaveException;
use CryptoApp\Models\Transaction;

interface TransactionRepository
{
    /**
     * @throws DatabaseException
     * @throws TransactionSaveException
     */
    public function save(Transaction $transaction): void;
    public function get(): array;
    public function getByUserId(string $userId): array;
}

