<?php

namespace CryptoApp\Controllers;

use CryptoApp\Services\WalletService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WalletController
{
    private WalletService $walletService;
    private SessionInterface $session;

    public function __construct(WalletService $walletService, SessionInterface $session)
    {
        $this->walletService = $walletService;
        $this->session = $session;
    }

    public function displayWallet(Request $request): array
    {
        $walletState = $this->walletService->calculateWalletState();

        return [
            'template' => 'WalletState.twig',
            'data' => [
                'wallet' => $walletState,
            ],
        ];
    }
}