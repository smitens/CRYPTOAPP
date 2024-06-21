<?php

namespace CryptoApp\Repositories\User;

use CryptoApp\Exceptions\DatabaseException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Models\User;
use Exception;
use Medoo\Medoo;

class SqliteUserRepository implements UserRepository
{
    private Medoo $database;

    public function __construct(string $databaseFile = 'storage/database.sqlite')
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
            $this->database->exec('CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL
            )');

        } catch (Exception $e) {
            throw new DatabaseException("Error creating table: " . $e->getMessage());

        }
    }

    public function save(User $user): void
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

    public function getById(string $userId): ?array
    {
        try {
            $userData = $this->database->get('users', '*', ['id' => $userId]);
            return $userData ?? null;
        } catch (Exception $e) {
            throw new UserNotFoundException("User no found: " . $e->getMessage());
        }
    }

    public function getByUsernameAndPassword (string $username, string $password): ?array {
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
