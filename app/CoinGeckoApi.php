<?php
namespace CryptoApp\App;
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinGeckoApi implements ApiClientInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coingecko.com/api/v3/',
            'timeout' => 2.0,
        ]);
    }

    public function getTopCryptoCurrencies(int $limit = 10): array
    {
        try {
            $response = $this->client->request('GET', 'coins/markets', [
                'query' => [
                    'vs_currency' => 'usd',
                    'per_page' => $limit,
                    'page' => 1,
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                $result = [];

                foreach ($data as $coin) {
                    $currency = new Currency(
                        $coin['market_cap_rank'] ?? null,
                        $coin['name'],
                        $coin['symbol'],
                        $coin['current_price'],
                    );
                    $result[] = $currency;
                }

                return $result;
            } else {
                throw new \Exception('Failed to get data from CoinGecko. Status Code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }

    public function searchCryptoCurrencies(string $symbol): Currency
    {
        try {
            $response = $this->client->request('GET', 'coins/list', [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]);

            $coinsList = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                $coinId = null;

                foreach ($coinsList as $coin) {
                    if (strtolower($coin['symbol']) === strtolower($symbol)) {
                        $coinId = $coin['id'];
                        break;
                    }
                }

                if ($coinId === null) {
                    throw new \Exception('Coin with symbol ' . $symbol . ' not found.');
                }

                $coinResponse = $this->client->request('GET', 'coins/' . $coinId, [
                    'query' => [
                        'localization' => 'false',
                    ],
                ]);

                $coinData = json_decode($coinResponse->getBody(), true);


                if ($coinResponse->getStatusCode() === 200) {
                    $coinInfo = $coinData['market_data'];

                    $rank = $coinData['market_cap_rank'] ?? null;
                    $name = $coinData['name'];
                    $symbol = $coinData['symbol'];
                    $price = $coinInfo['current_price']['usd'];

                    return new Currency($rank, $name, $symbol, $price);
                } else {
                    throw new \Exception('Failed to get data from CoinGecko. Status Code: ' . $coinResponse->getStatusCode());
                }
            } else {
                throw new \Exception('Failed to get currency list from CoinGecko. Status Code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}