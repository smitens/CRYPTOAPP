# CryptoApp

CryptoApp is a terminal based PHP application that allows you to manage and trade cryptocurrencies using virtual money. You can list the top cryptocurrencies, search for specific cryptocurrencies by their ticker symbol, buy and sell cryptocurrencies, display the current state of your wallet, and view your transaction history. All transactions are saved in a JSON file for persistence.

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

2. Install the required dependencies using Composer:

    ```sh
    composer install
    ```
3. Ensure the `transactions.json` file is writable:

    ```sh
    touch transactions.json
    chmod 666 transactions.json
    ```

## Usage

Run the application using the following command:

```sh
php index.php
