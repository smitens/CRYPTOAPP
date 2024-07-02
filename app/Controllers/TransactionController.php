<?php

namespace CryptoApp\Controllers;


use CryptoApp\Repositories\Transaction\TransactionRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use CryptoApp\Response;

class TransactionController
{

    private TransactionRepository $transactionRepository;
    private SessionInterface $session;


    public function __construct(

        TransactionRepository $transactionRepository,
        SessionInterface $session
    ) {

        $this->transactionRepository = $transactionRepository;
        $this->session = $session;

    }


    public function index(): Response
    {
        if (!$this->session->has('user')) {
            return new Response('error.twig', ['message' => 'User not logged in.']);
        }

        $user = $this->session->get('user');
        $userId = $user->getId();

        $transactions = $this->transactionRepository->getByUserId($userId);

        return new Response('transactions\index.twig', ['transactions' => $transactions]);
    }

}
