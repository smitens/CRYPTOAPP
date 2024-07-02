<?php

namespace CryptoApp\Controllers;

use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Services\UserService;
use CryptoApp\Services\WalletService;
use CryptoApp\Models\Wallet;
use CryptoApp\Repositories\Wallet\WalletRepository;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use CryptoApp\Response;
use CryptoApp\Exceptions\UserLoginException;

class UserController
{
    private UserService $userService;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;
    private CurrencyRepository $currencyRepository;
    private SessionInterface $session;

    public function __construct(
        UserService $userService,
        WalletRepository $walletRepository,
        TransactionRepository $transactionRepository,
        CurrencyRepository $currencyRepository,
        SessionInterface $session
    ) {
        $this->userService = $userService;
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
        $this->currencyRepository = $currencyRepository;
        $this->session = $session;
    }

    public function showRegisterForm(): Response
    {
        return new Response(
            'userservice/register.twig',
            []
        );
    }

    public function register(Request $request): Response
    {
        try {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            $user = $this->userService->createUser($username, $password);

            $userId = $user->getId();

            $wallet = new Wallet($userId, 1000.0);

            $walletService = new WalletService(
                $wallet,
                $this->walletRepository,
                $this->transactionRepository,
                $this->currencyRepository,
                $userId
            );

            $walletService->createWallet();

            return new Response(
                'userservice/registersuccess.twig',
                ['message' => 'User registered successfully!']
            );
        } catch (UserSaveException $e) {
            return new Response(
                'userservice/register.twig',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function showLoginForm(): Response
    {
        return new Response(
            'userservice/login.twig',
            []
        );
    }

    public function login(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        try {
            $user = $this->userService->login($username, $password);

            $this->session->set('user', $user);

            return new Response(
                'userservice/loginsuccess.twig',
                ['message' => 'Login successful!', 'user' => $user]
            );
        } catch (UserLoginException $e) {
            return new Response(
                'userservice/login.twig',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function logout(): Response
    {
        $username = '';
        if ($this->session->has('user')) {
            $username = $this->session->get('user')->getUsername();

            $this->session->clear();
        }

        return new Response(
            'userservice/logout.twig',
            ['username' => $username, 'message' => 'logged out successfully!']
        );
    }
}