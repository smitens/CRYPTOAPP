<?php

namespace CryptoApp\Repositories\Wallet;

use CryptoApp\Models\Wallet;
use CryptoApp\Exceptions\DatabaseException;
use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Exceptions\WalletUpdateException;

interface WalletRepository
{
    /**
     * @throws DatabaseException
     * @throws UserSaveException
     * @throws WalletNotFoundException
     * @throws WalletUpdateException
     */
    public function save(Wallet $wallet): void;
    public function get(string $userId): ?array;
    public function update(Wallet $wallet): void;
}

