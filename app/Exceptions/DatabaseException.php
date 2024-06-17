<?php

namespace CryptoApp\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    protected $message = 'Database error.';
}
