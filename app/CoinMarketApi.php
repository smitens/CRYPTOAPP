<?php
namespace CryptoApp\App;
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinMarketApi implements ApiClientInterface
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

    public function getTopCryptoCurrencies(int $limit = 10): array
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
                $topCoins = $data['data'];
                $result = [];

                foreach ($topCoins as $coin) {
                    $coinDetailsResponse = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
                        'query' => [
                            'symbol' => $coin['symbol'],
                            'convert' => 'USD'
                        ],
                        'headers' => [
                            'X-CMC_PRO_API_KEY' => $this->apiKey,
                        ],
                    ]);

                    $coinDetails = json_decode($coinDetailsResponse->getBody(), true);
                    $coinDetail = $coinDetails['data'][$coin['symbol']];

                    $currency = new Currency(
                        $coin['cmc_rank'],
                        $coin['name'],
                        $coin['symbol'],
                        $coinDetail['quote']['USD']['price'],
                    );
                    $result[] = $currency;
                }

                return $result;
            } else {
                throw new \Exception('Failed to get data from CoinMarketCap. Status Code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }

    public function searchCryptoCurrencies(string $symbol): Currency
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
                $coinDetail = $data['data'][$symbol];
                return new Currency(
                    $coinDetail ['cmc_rank'],
                    $coinDetail['name'],
                    $coinDetail['symbol'],
                    $coinDetail['quote']['USD']['price']
                );
            } else {
                throw new \Exception('Failed to get data from CoinMarketCap. Status Code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}