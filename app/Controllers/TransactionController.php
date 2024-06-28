<?php

namespace CryptoApp\Controllers;

use CryptoApp\Services\BuyCurrencyService;
use CryptoApp\Services\SellCurrencyService;
use CryptoApp\Services\WalletService;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use CryptoApp\Repositories\Currency\CurrencyRepository;

class TransactionController
{
    private BuyCurrencyService $buyCurrencyService;
    private SellCurrencyService $sellCurrencyService;
    private TransactionRepository $transactionRepository;
    private SessionInterface $session;
    private CurrencyRepository $currencyRepository;
    private WalletService $walletService;

    public function __construct(
        BuyCurrencyService $buyCurrencyService,
        SellCurrencyService $sellCurrencyService,
        TransactionRepository $transactionRepository,
        SessionInterface $session,
        CurrencyRepository $currencyRepository,
        WalletService $walletService
    ) {
        $this->buyCurrencyService = $buyCurrencyService;
        $this->sellCurrencyService = $sellCurrencyService;
        $this->transactionRepository = $transactionRepository;
        $this->session = $session;
        $this->currencyRepository = $currencyRepository;
        $this->walletService = $walletService;
    }

    public function showBuyForm(): array
    {
        $topCurrencies = $this->currencyRepository->getTop();
        return [
            'template' => 'BuyForm.twig',
            'data' => [
                'topCurrencies' => $topCurrencies
            ]
        ];
    }

    public function buy(Request $request): array
    {
        $symbol = $request->request->get('symbol');
        $amount = (float) $request->request->get('amount');

        $this->buyCurrencyService->execute($symbol, $amount);

        return [
            'template' => 'BuySuccess.twig',
            'data' => ['symbol' => $symbol, 'amount' => $amount]
        ];
    }

    public function showSellForm(): array
    {
        return [
            'template' => 'SellForm.twig',
            'data' => []
        ];
    }

    public function sell(Request $request): array
    {
        $symbol = $request->request->get('symbol');
        $amount = (float) $request->request->get('amount');

        $this->sellCurrencyService->execute($symbol, $amount);

        return [
            'template' => 'SellSuccess.twig',
            'data' => ['symbol' => $symbol, 'amount' => $amount]
        ];
    }

    public function displayTransactions(Request $request): array
    {
        if (!$this->session->has('user')) {
            return [
                'template' => 'error.twig',
                'data' => ['message' => 'User not logged in.']
            ];
        }

        $user = $this->session->get('user');
        $userId = $user->getId();

        $transactions = $this->transactionRepository->getByUserId($userId);

        return [
            'template' => 'Transactions.twig',
            'data' => ['transactions' => $transactions]
        ];
    }
}