<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController {
//    private $security;
//
//    public function __construct(Security $security) {
//        $this->security = $security;
//    }
    #[Route('/profile', name: 'user_profile')]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $logger->info('UserController::edit');

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $logger->info($user->__toString());
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/events', name: 'user_events')]
    public function myEvent(): Response
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your events.');
        }
        $events = $user->getEvents();
        return $this->render('user/user_events.html.twig', [
            'events' => $events,
        ]);
    }
}
