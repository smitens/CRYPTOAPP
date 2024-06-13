<?php

require_once 'vendor/autoload.php';

use CryptoApp\App\CoinMarketApi;
use CryptoApp\App\CoinGeckoApi;
use CryptoApp\App\CoinPaprikaApi;
use CryptoApp\App\JsonTransactionsService;
use CryptoApp\App\SqliteTransactionsService;
use CryptoApp\App\Wallet;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$apiKey = $_ENV['APIKEY'];
$api = new CoinPaprikaApi();

echo "Choose storage type:\n";
echo "1. JSON File\n";
echo "2. SQLite Database\n";
echo "Enter the number of your choice: ";
$storageChoice = trim(fgets(STDIN));

if ($storageChoice == 1) {
    $transactions = new JsonTransactionsService('transactions.json', $api);
} elseif ($storageChoice == 2) {
    $transactions = new SqliteTransactionsService('storage/database.sqlite', $api);
} else {
    exit("Invalid choice.\n");
}

$wallet = new Wallet(1000, $transactions);

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
            } catch (\Exception $e) {
                echo "An error occurred: " . $e->getMessage() . "\n";
            }
            break;

        case 2:
            try {
            $symbol = trim(readline("Enter the symbol: "));
            $currencyInfo = $api->searchCryptoCurrencies($symbol);
            echo "Currency Name: " . $currencyInfo->getName() . "\n";
            echo "Currency Symbol: " . $currencyInfo->getSymbol() . "\n";
            echo "Current Price (USD): " . number_format($currencyInfo->getPrice(), 8) .
                "\n";
            } catch (\Exception $e) {
                echo "An error occurred: " . $e->getMessage() . "\n";
            }
            break;

        case 3:
            $symbol = trim(readline("Enter the symbol to buy: "));
            $amount = floatval(trim(readline("Enter the amount to buy: ")));
            $balance = $wallet->getBalance();
            $cryptoData = $api->searchCryptoCurrencies($symbol);
            $price = $cryptoData->getPrice();
            $totalCost = $price * $amount;
            if ($balance >= $totalCost) {
                $transactions->buy($symbol, $amount);
            } else {
                echo "\033[31mInsufficient balance. Please try again with a lower amount.\033[0m\n";
            }
            break;

        case 4:
            $symbol = trim(readline("Enter the symbol to sell: "));
            $amount = floatval(trim(readline("Enter the amount to sell: ")));
            $transactions->sell($symbol, $amount);
            break;

        case 5:
            $transactions->displayTransactions();
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