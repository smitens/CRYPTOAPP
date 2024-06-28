<?php

namespace CryptoApp\Controllers;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OptionController
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function options(): array
    {
        if (!$this->session->has('user')) {
            return [
                'template' => 'login.twig',
                'data' => [
                    'message' => 'Please log in to access options.'
                ]
            ];
        }

        $user = $this->session->get('user');

        $template = 'OptionsList.twig';

        return [
            'template' => $template,
            'data' => [
                'user' => $user,
                'message' => 'Welcome to options!',
            ],
        ];
    }
}