<?php

namespace CryptoApp\Database;

use CryptoApp\Models\Transaction;

interface DatabaseInterface
{
    public function save (Transaction $transaction): void;
    public function getAll (): array;

}
