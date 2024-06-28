<?php
namespace CryptoApp\Services;

use CryptoApp\Exceptions\UserLoginException;
use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Models\User;
use CryptoApp\Repositories\User\UserRepository;
use Exception;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(string $username, string $password): User
    {
        try {
            $hashedPassword = md5($password);
            $user = new User($username, $hashedPassword);
            $this->userRepository->save($user);
            return $user;
        } catch (Exception $e) {
            throw new UserSaveException("Registration failed: " . $e->getMessage());
        }
    }

    public function login(string $username, string $password): ?User
    {
        try {
            $userData = $this->userRepository->getByUsernameAndPassword($username, $password);

            if (!$userData) {
                throw new UserLoginException('Invalid username or password.');
            }

            return new User(
                $userData['username'],
                $userData['password'],
                $userData['id']
            );
        } catch (Exception $e) {
            throw new UserLoginException("Login failed: " . $e->getMessage());
        }
    }
}