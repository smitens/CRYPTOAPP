<?php

namespace CryptoApp\Controllers;

use CryptoApp\Services\BuyCurrencyService;
use CryptoApp\Services\SellCurrencyService;
use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Exceptions\NoSuchCurrencyException;
use CryptoApp\Exceptions\InsufficientBalanceException;
use CryptoApp\Exceptions\InsufficientCryptoAmountException;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use Symfony\Component\HttpFoundation\Request;
use CryptoApp\Response;
use CryptoApp\RedirectResponse;

class CurrencyController {

    private BuyCurrencyService $buyCurrencyService;
    private SellCurrencyService $sellCurrencyService;
    private CurrencyRepository $currencyRepository;

    public function __construct
    (
        BuyCurrencyService $buyCurrencyService,
        SellCurrencyService $sellCurrencyService,
        CurrencyRepository $currencyRepository
    )
    {
        $this->buyCurrencyService = $buyCurrencyService;
        $this->sellCurrencyService = $sellCurrencyService;
        $this->currencyRepository = $currencyRepository;
    }

    public function index(): Response
    {
        try {
            $topCryptos = $this->currencyRepository->getTop();
            return new Response ('currencies/index.twig', ['topCryptos' => $topCryptos]);

        } catch (HttpFailedRequestException $e) {
            return new Response('error.twig', [
                'message' => 'Problems loading page, try again',
                'redirectLink' => '/top-currencies'
            ]);
        }
    }

    public function show (Request $request, array $vars): Response
    {
        $symbol = $vars['symbol'];

        try {
            $cryptoData = $this->currencyRepository->search($symbol);
        } catch (NoSuchCurrencyException $e) {
            return new Response ('error.twig', ['errorMessage' => 'Currency not found: ' . $symbol]);
        }

        return new Response ('currencies/show.twig', ['cryptoData' => $cryptoData]);

    }

    public function buy(Request $request,  array $vars): RedirectResponse
    {
        $symbol = $vars['symbol'];

        try {
        $amount = (float) $request->request->get('amount');

        $this->buyCurrencyService->execute($symbol, $amount);

        return new RedirectResponse('\transactions');

        } catch (InsufficientBalanceException $e) {

            $errorMessage = $e->getMessage();

            return new RedirectResponse('/top-currencies/' . $symbol . '?error=' . urlencode($errorMessage));
        }
    }

    public function sell(Request $request, array $vars): RedirectResponse
    {
        $symbol = $vars['symbol'];

        try {
            $amount = (float)$request->request->get('amount');

            $this->sellCurrencyService->execute($symbol, $amount);

            return new RedirectResponse('\transactions');

        } catch (InsufficientCryptoAmountException $e) {

            $errorMessage = $e->getMessage();

            return new RedirectResponse('/top-currencies/' . $symbol . '?error=' . urlencode($errorMessage));
        }
    }
}