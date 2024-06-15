<?php

namespace App\Controller;

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

    private EntityManager $entityManager;
    private UrlGeneratorInterface $generator;
    public function __construct(EntityManagerInterface $entityManager,private LoggerInterface $logger, UrlGeneratorInterface $generator) {
        $this->entityManager = $entityManager;
        $this->generator = $generator;
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

    #[Route('event/stripe/success/{id}', name: 'payment_success')]
    public function stripeSuccess($id, EntityManagerInterface $entityManager, UserRepository $repo, MailerInterface $mailer, Event $event):RedirectResponse{
        //recup user
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        $event->addParticipant($user);
        $user->addEvent($event);
        $entityManager->persist($user);
        $entityManager->persist($event);
        $entityManager->flush();

        $email = (new NotificationEmail())
            ->from('melanbenoit60@gmail.com')
            ->to($user->getEmail())
            ->subject('Inscription à l\'événement')
            ->text('Vous vous êtes inscrit à l\'événement ' .$event->getTitle())
            ->html('Vous vous êtes inscrit à l\'événement ' .$event->getTitle());

        $mailer->send($email);
        return $this->redirectToRoute('user_events');
    }

    #[Route('event/stripe/echec/{id}', name: 'payment_echec')]
    public function stripeEchec($id):RedirectResponse{
        //evenement detail
        return $this->render('payment/success.html.twig');
    }
}