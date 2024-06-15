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
use Stripe\Checkout\Session;
use App\Repository\UserRepository;


class PaymentController extends AbstractController {

    private EntityManager $entityManager;
    public function __construct(EntityManagerInterface $entityManager,private LoggerInterface $logger) {
        $this->entityManager = $entityManager;  
    }

    #[Route('event/stripe/{id}', name: 'payment')]
    public function stripe($id, UserRepository $repo):RedirectResponse{
        $event = $this->entityManager->getRepository(Event::class)->find($id);
        if(!$event){
            return $this->redirectToRoute('');
        }

        Stripe::setApiKey('sk_test_51PRW8HAXcJK6KQYTkLgg7ryENv8Pm2gN2UdsEjqKZNavv0pe8jf9mHpPWohglEM1Znuflriuhi5XsFsQwaNtjriu00zlLwwIxt');

        //recup user
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        // Création de session checkout --> contrôle les informations que le client peut consulter sur la page de paiment
        $checkout_session = Session::create([
            'customer_email' => $user->getEmail(),
            'line_items' => [[
              # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
              'price' => $event->getPrice(),
              'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success.html',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);


        // A rajouter si le paiement est un succès
        // $event->addParticipant($user);
        // $user->addEvent($event);
        // $entityManager->persist($user);
        // $entityManager->persist($event);
        // $entityManager->flush();

        // $email = (new NotificationEmail())
        //     ->from('melanbenoit60@gmail.com')
        //     ->to($user->getEmail())
        //     ->subject('Inscription à l\'événement')
        //     ->text('Vous vous êtes inscrit à l\'événement ' .$event->getTitle())
        //     ->html('Vous vous êtes inscrit à l\'événement ' .$event->getTitle());

        // $mailer->send($email);

    }
}