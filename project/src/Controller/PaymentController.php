<?php

namespace App\Controller;

use App\Service\MailService;
use Doctrine\ORM\EntityManager;
use App\Entity\Event;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;

class PaymentController extends AbstractController {

    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $generator;
    private MailService $mailService;
    public function __construct(EntityManagerInterface $entityManager,private LoggerInterface $logger, UrlGeneratorInterface $generator, MailService $mailService) {
        $this->entityManager = $entityManager;
        $this->generator = $generator;
        $this->mailService = $mailService;
    }

    #[Route('event/stripe/{id}', name: 'payment')]
    public function stripe($id, UserRepository $repo):RedirectResponse{
        $event = $this->entityManager->getRepository(Event::class)->find($id);
        if(!$event){
            return $this->redirectToRoute(''); //TODO
        }

        Stripe::setApiKey('sk_test_51PRW8HAXcJK6KQYTkLgg7ryENv8Pm2gN2UdsEjqKZNavv0pe8jf9mHpPWohglEM1Znuflriuhi5XsFsQwaNtjriu00zlLwwIxt');

        //recup user
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        //Créer un produit stripe
        $product = [
            'price_data' => [
                'currency'=> 'eur',
                'unit_amount' => ($event->getPrice())* 100, //prix en centimes
                'product_data'=> [
                   'name'=> $event->getTitle(),
                ]
            ],
                'quantity' => 1,
        ];

        // Création de session checkout --> contrôle les informations que le client peut consulter sur la page de paiment
        $checkout_session = Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'line_items'=>[$product],
            'mode' => 'payment',
            'success_url' => $this->generator->generate('payment_success',[
                'id'=> $event->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generator->generate('payment_echec',[
                'id'=> $event->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('event/stripe/success/{id}', name: 'payment_success')]
    public function stripeSuccess($id, EntityManagerInterface $entityManager, UserRepository $repo, Event $event):RedirectResponse{
        //recup user
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        $event->addParticipant($user);
        $user->addEvent($event);
        $entityManager->persist($user);
        $entityManager->persist($event);
        $entityManager->flush();

        $this->mailService->sendEmail($user->getEmail(), 'Inscription à l\'événement', 'Vous vous êtes inscrit à l\'événement ' .$event->getTitle().'le paiement a été validé avec succès');

        $this->addFlash('success', 'Le paiement a été validé avec succès et vous êtes inscrit à l\'événement '.$event->getTitle());
        return $this->redirectToRoute('user_events');
    }

    #[Route('event/stripe/echec/{id}', name: 'payment_echec')]
    public function stripeEchec($id):RedirectResponse{
        $this->addFlash('danger', 'Le paiement a échoué veuillez cliquer sur S\'inscrire pour réessayer');
        return $this->redirectToRoute('event_show', [
            'id' => $id,
        ]);
    }
}