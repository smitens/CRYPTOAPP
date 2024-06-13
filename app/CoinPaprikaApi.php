<?php

namespace CryptoApp\App;
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinPaprikaApi implements ApiClientInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coinpaprika.com/v1/',
            'timeout' => 2.0,
        ]);
    }

    public function getTopCryptoCurrencies(int $limit = 10): array
    {
        try {
            $response = $this->client->request('GET', 'coins');

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                $topCoins = array_slice($data, 0, $limit);
                $result = [];

                foreach ($topCoins as $coin) {
                    $coinDetailsResponse = $this->client->request('GET', 'tickers/' . $coin['id']);
                    $coinDetails = json_decode($coinDetailsResponse->getBody(), true);

                    $currency = new Currency(
                        $coinDetails['rank'],
                        $coin['name'],
                        $coin['symbol'],
                        $coinDetails['quotes']['USD']['price'],
                    );
                    $result[] = $currency;
                }

                return $result;
            } else {
                throw new \Exception('Failed to get data from CoinPaprika. Status Code: ' .
                    $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }

    public function searchCryptoCurrencies(string $symbol): Currency
    {
        try {
            $response = $this->client->request('GET', 'coins');
            $coinsList = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to get data from CoinPaprika. Status Code: ' . $response->getStatusCode());
            }

            $coinId = null;
            foreach ($coinsList as $coin) {
                if (strtolower($coin['symbol']) === strtolower($symbol)) {
                    $coinId = $coin['id'];
                    break;
                }
            }

            if ($coinId === null) {
                throw new \Exception('Coin not found');
            }

            $response = $this->client->request('GET', 'tickers/' . $coinId);
            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                return new Currency (
                    $data['rank'],
                    $data['name'],
                    $data['symbol'],
                    $data['quotes']['USD']['price'],
                );
            } else {
                throw new \Exception('Failed to get data from CoinPaprika. Status Code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}