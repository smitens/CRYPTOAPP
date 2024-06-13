# CryptoApp

CryptoApp is a terminal based PHP application that allows you to manage and trade cryptocurrencies with virtual money using data from one of three service - CoinMarketCap API, CoinPaprika API, CoinGecko API. You can list the top cryptocurrencies, search for specific cryptocurrencies by their ticker symbol, buy and sell cryptocurrencies, display the current state of your wallet, and view your transaction history. All transactions are saved - you can choose to save them in a JSON file or SQLLite DB for persistence.

## Features

- List top cryptocurrencies
- Search cryptocurrency by ticker symbol
- Purchase cryptocurrency using virtual money (starting with $1000 as the base)
- Sell cryptocurrency
- Display the current state of your wallet based on transaction history
- Display the transaction list

## Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/smitens/CRYPTOAPP.git
    ```
   ```sh
   Install dependencies using Composer:
   composer install
   ```
   ```sh
    Create a .env file in the root directory and add your CoinMarketCap API key:
    APIKEY=your_api_key_here
    ```

2. Install the required dependencies using Composer:

    ```sh
    composer install
    ```

## Usage

Run the application using the following command:

```sh
php index.php
