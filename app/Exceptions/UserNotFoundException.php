<?php

namespace CryptoApp\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'User not found.';
}

