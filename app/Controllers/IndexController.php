<?php

namespace CryptoApp\Controllers;

class IndexController
{
    public function index(): array
    {
        $template = 'index.twig';
        $data = [
            'title' => 'CryptoApp Home',
            'message' => 'Welcome to CryptoApp!',
        ];

        return [
            'template' => $template,
            'data' => $data,
        ];
    }
}