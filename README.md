# CryptoApp

CryptoApp is a terminal based PHP application that allows you as a registered user to manage and trade cryptocurrencies with virtual money using data of your choice from one of three services - CoinMarketCap API, CoinPaprika API, CoinGecko API. You can list the top cryptocurrencies, search for specific cryptocurrencies by their ticker symbol, buy and sell cryptocurrencies, display the current state of your wallet, and view your transaction history. All users (also user wallets) and transactions are saved in SQLite DB for persistence.

## Features

- Register and login
- List top cryptocurrencies
- Search cryptocurrency by ticker symbol
- Purchase cryptocurrency using virtual money (starting with $1000 as the base)
- Sell cryptocurrency
- Display the current state of your wallet (including profitability) based on transaction history
- Display the transaction list

## Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/smitens/CRYPTOAPP/tree/V5.git
    ```
   ```sh
    Create a .env file in the root directory and add your CoinMarketCap API key. Find example in .env.example file.
    ```
   ```sh
    If you choose CoinPaprika or CoinGecko as datasource, no API key will be required
    ```

2. Install the required dependencies using Composer:

    ```sh
    composer install
    ```
   ```sh
    data storage directory with database.sqlite file in it will be created after you run the application 
    (existing storage directory with test data file added in repository as an example)
    ```

## Usage

Run the application using the following command:

```sh
php index.php
