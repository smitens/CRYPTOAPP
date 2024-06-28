<?php

namespace CryptoApp\Controllers;

use CryptoApp\Exceptions\NoSuchCurrencyException;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use Symfony\Component\HttpFoundation\Request;

class CurrencyController {

    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function topCurrencies(): array
    {
        return [
            'template' => 'TopCurrencies.twig',
            'data' => [
                'topCryptos' => $this->currencyRepository->getTop(),
            ],
        ];
    }

    public function showSearchForm(): array
    {
        return [
            'template' => 'SearchForm.twig',
            'data' => [],
        ];
    }

    public function searchCurrency(Request $request, array $vars): array
    {
        $symbol = $vars['symbol'] ?? $request->request->get('symbol');

        try {
            $cryptoData = $this->currencyRepository->search($symbol);
        } catch (NoSuchCurrencyException $e) {
            return [
                'template' => 'Error.twig',
                'data' => [
                    'errorMessage' => 'Currency not found: ' . $symbol
                ]
            ];
        }

        return [
            'template' => 'CurrencyInfo.twig',
            'data' => [
                'cryptoData' => $cryptoData
            ]
        ];
    }
}