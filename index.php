<?php

require_once 'vendor/autoload.php';

use CryptoApp\Api\CoinMarketApi;
use CryptoApp\Api\CoinGeckoApi;
use CryptoApp\Api\CoinPaprikaApi;
use CryptoApp\Services\BuyCurrencyService;
use CryptoApp\Services\SellCurrencyService;
use CryptoApp\Database\SqliteDatabase;
use CryptoApp\Wallet;
use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$apiKey = $_ENV['APIKEY'];
$api = new CoinMarketApi($apiKey);
$database = new SqliteDatabase();

$wallet = new Wallet(1000, $database);

while (true) {
    echo "\n\033[1m\033[4mCRYPTO CURRENCY APP\033[0m\n\n";
    echo "1. Display TOP 10 crypto currencies\n";
    echo "2. Search for crypto currency using its symbol\n";
    echo "3. Buy crypto currency\n";
    echo "4. Sell crypto currency\n";
    echo "5. Display list of transactions\n";
    echo "6. Display current state of Wallet\n";
    echo "7. Exit\n";
    echo "\n";
    echo "Enter the number of your choice: ";
    $choice = trim (fgets(STDIN));

    switch ($choice) {
        case 1:
            try {
            $topCryptos = $api->getTopCryptoCurrencies();
            $output = new ConsoleOutput();
            $table = new Table($output);
                $table->setHeaders(['Rank', 'Name', 'Symbol', 'Price']);

                foreach ($topCryptos as $crypto) {
                    $table->addRow([
                        $crypto->getRank(),
                        $crypto->getName(),
                        $crypto->getSymbol(),
                        number_format($crypto->getPrice(), 8),
                    ]);
                }
            $table->render();
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage() . "\n";
            }
            break;

        case 2:
            try {
            $symbol = strtoupper(trim(readline("Enter the symbol: ")));
            $currencyInfo = $api->searchCryptoCurrencies($symbol);
            echo "Currency Name: " . $currencyInfo->getName() . "\n";
            echo "Currency Symbol: " . $currencyInfo->getSymbol() . "\n";
            echo "Current Price (USD): " . number_format($currencyInfo->getPrice(), 8) .
                "\n";
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage() . "\n";
            }
            break;

        case 3:
            $symbol = strtoupper(trim(readline("Enter the symbol to buy: ")));
            $amount = floatval(trim(readline("Enter the amount to buy: ")));
            $balance = $wallet->getBalance();
            $cryptoData = $api->searchCryptoCurrencies($symbol);
            $price = $cryptoData->getPrice();
            $totalCost = $price * $amount;
            if ($balance >= $totalCost) {

                $service = (new BuyCurrencyService(
                    $api,
                    $database
                ));
                $service->execute($symbol, $amount);
                echo "Thank You! You just bought $amount $symbol!\n";
            } else {
                echo "\033[31mInsufficient balance. Please try again with a lower amount.\033[0m\n";
            }
            break;

        case 4:
            $symbol = strtoupper(trim(readline("Enter the symbol to sell: ")));
            $amount = floatval(trim(readline("Enter the amount to sell: ")));

            $service = (new SellCurrencyService(
                $api,
                $database
            ));
            $service->execute($symbol, $amount);
            echo "Thank You! You just sold $amount $symbol!\n";
            break;

        case 5:
            $output = new ConsoleOutput();
            $table = new Table($output);
            $table->setHeaders(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp']);

            foreach ($database->getAll() as $transaction) {
                $table->addRow([
                    $transaction->getType(),
                    $transaction->getSymbol(),
                    $transaction->getAmount(),
                    number_format($transaction->getPrice(), 8),
                    Carbon::createFromTimestamp($transaction->getTimestamp())->format('Y-m-d H:i:s'),
                ]);
            }
            $table->render();
            break;

        case 6:
            $wallet->displayWalletState();
            break;

        case 7:
            exit;

        default:
            echo "Invalid choice. Please try again.\n";
            break;
    }
}