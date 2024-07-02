<?php

return [
    ['GET', '/', 'IndexController@index'],
    ['GET', '/options', 'OptionController@options'],
    ['GET', '/register', 'UserController@showRegisterForm'],
    ['POST', '/register', 'UserController@register'],
    ['GET', '/login', 'UserController@showLoginForm'],
    ['POST', '/login', 'UserController@login'],
    ['GET', '/logout', 'UserController@logout'],
    ['POST', '/logout', 'UserController@logout'],
    ['GET', '/top-currencies', 'CurrencyController@index'],
    ['GET', '/top-currencies/{symbol}', 'CurrencyController@show'],
    ['POST', '/top-currencies/{symbol}/buy', 'CurrencyController@buy'],
    ['POST', '/top-currencies/{symbol}/sell', 'CurrencyController@sell'],
    ['GET', '/transactions', 'TransactionController@index'],
    ['GET', '/wallet', 'WalletController@index'],
];

