<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Stripe;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;


class PaymentController extends AbstractController {

    private EntityManager $entityManager;
    public function __construct(EntityManagerInterface $entityManager,private LoggerInterface $logger) {
        $this->entityManager = $entityManager;  
    }

    #[Route('/order/create-session-stripe{id}', name: 'payment')]
    public function stripe($id):RedirectResponse{
        $event = $this->entityManager->getRepository(Event::class)->find($id);
        dd($event);

        // \Stripe\Stripe::setApiKey(sk_test_51PRW8HAXcJK6KQYTkLgg7ryENv8Pm2gN2UdsEjqKZNavv0pe8jf9mHpPWohglEM1Znuflriuhi5XsFsQwaNtjriu00zlLwwIxt $stripeSecretKey);

        // $checkout_session = \Stripe\Checkout\Session::create([
        // 'line_items' => [[
        //     # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
        //     'price' => '{{PRICE_ID}}',
        //     'quantity' => 1,
        // ]],
        // 'mode' => 'payment',
        // 'success_url' => $YOUR_DOMAIN . '/success.html',
        // 'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        // ]);

    }
}