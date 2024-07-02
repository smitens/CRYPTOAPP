<?php

namespace CryptoApp\Controllers;

use CryptoApp\Response;

class IndexController
{
    public function index(): Response
    {
        $template = 'index.twig';
        $data = [
            'title' => 'CryptoApp Home',
            'message' => 'Welcome to CryptoApp!',
        ];

        return new Response($template, $data);
    }
}
