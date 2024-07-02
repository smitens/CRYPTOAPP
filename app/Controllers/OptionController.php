<?php

namespace CryptoApp\Controllers;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use CryptoApp\Response;

class OptionController
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function options(): Response
    {
        if (!$this->session->has('user')) {
            return new Response('login.twig', [
                'message' => 'Please log in to access options.'
            ]);
        }

        $user = $this->session->get('user');

        $template = 'options.twig';

        return new Response($template, [
            'user' => $user,
            'message' => 'Welcome to options!',
        ]);
    }
}