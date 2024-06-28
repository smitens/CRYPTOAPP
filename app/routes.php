<?php

return [
    ['GET', '/register', 'UserController@showRegisterForm'],
    ['POST', '/register', 'UserController@register'],
    ['GET', '/login', 'UserController@showLoginForm'],
    ['POST', '/login', 'UserController@login'],
    ['GET', '/logout', 'UserController@logout'],
    ['POST', '/logout', 'UserController@logout'],
    ['GET', '/top-currencies', 'CurrencyController@topCurrencies'],
    ['GET', '/search-currency', 'CurrencyController@showSearchForm'],
    ['POST', '/search-currency', 'CurrencyController@searchCurrency'],
    ['GET', '/', 'IndexController@index'],
    ['GET', '/options', 'OptionController@options'],
    ['GET', '/buy', 'TransactionController@showBuyForm'],
    ['POST', '/buy', 'TransactionController@buy'],
    ['GET', '/sell', 'TransactionController@showSellForm'],
    ['POST', '/sell', 'TransactionController@sell'],
    ['GET', '/transactions', 'TransactionController@displayTransactions'],
    ['GET', '/wallet', 'WalletController@displayWallet'],
];

