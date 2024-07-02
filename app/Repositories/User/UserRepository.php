<?php

namespace CryptoApp\Repositories\User;

use CryptoApp\Models\User;
use CryptoApp\Exceptions\DatabaseException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\UserSaveException;

interface UserRepository
{
    /**
     * @throws DatabaseException
     * @throws UserNotFoundException
     * @throws UserSaveException
     */
    public function save(User $user): void;
    public function getById(string $userId): ?array;
    public function getByUsernameAndPassword (string $username, string $password): ?array;
}

