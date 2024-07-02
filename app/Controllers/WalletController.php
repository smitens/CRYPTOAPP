<?php

namespace CryptoApp\Controllers;

use CryptoApp\Services\WalletService;
use CryptoApp\Response;
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

    public function index(): Response
    {
        if (!$this->session->has('user')) {
            return new Response('error.twig', ['message' => 'User not logged in.']);
        }

        $walletState = $this->walletService->calculateWalletState();

        return new Response(
            'wallet\index.twig',
            ['wallet' => $walletState]
        );
    }
}