<?php
namespace CryptoApp\App;
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiToData
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'timeout' => 2.0,
        ]);
        $this->apiKey = $apiKey;
    }

    public function getTopCryptoCurrencies($limit = 10): array
    {
        try {
        $response = $this->client->request('GET', 'cryptocurrency/listings/latest', [
            'query' => [
                'start' => '1',
                'limit' => $limit,
                'convert' => 'USD'
            ],
            'headers' => [
                'X-CMC_PRO_API_KEY' => $this->apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200) {
            return $data['data'];
        } else {
            throw new \Exception('Failed to get data from CoinMarketCap. Status Code: ' .
                $response->getStatusCode());
        }
    } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }

    public function searchCryptoCurrencies($symbol): array
    {
        try {
        $response = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
            'query' => [
                'symbol' => $symbol,
                'convert' => 'USD'
            ],
            'headers' => [
                'X-CMC_PRO_API_KEY' => $this->apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200) {
            return $data['data'];
        } else {
            throw new \Exception('Failed to get data from CoinMarketCap. Status Code: ' .
                $response->getStatusCode());
        }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}