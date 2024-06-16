<?php

namespace App\Controller;

use App\Entity\Event;
use App\Security\EventVoter;
use App\Service\MailService;
use Psr\Log\LoggerInterface;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventsController extends AbstractController {
    private MailService $mailService;
    private EntityManagerInterface $entityManager;
    private EventRepository $repo;
    public function __construct(private LoggerInterface $logger, MailService $mailService, EntityManagerInterface $entityManager, EventRepository $eventRepository) {
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->repo = $eventRepository;
    }

    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response {
        $user = $this->getUser(); // Obtenir l'utilisateur authentifié

        return $this->render('events/home.html.twig', [
            'user' => $user,
            'events' => $this->repo->findAll()
        ]);
    }

    #[Route('/events/new', name: 'event_new')]
    public function new(Request $request): Response {
        $event = new Event();
        if (!$this->isGranted(EventVoter::CREATE, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de créer un événement, veuillez vous connecter.");
            return $this->redirectToRoute('app_login');
        }
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreator($this->getUser());

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            $this->addFlash('success', 'Vous avez créé l\'événement '.$event->getTitle());

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }
        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/edit', name: "event_edit")]
    public function edit(Request $request, Event $event): Response {
        if (!$this->isGranted(EventVoter::EDIT, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de modifier cet événement.");
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Vous avez modifié l\'événement '.$event->getTitle());
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/delete', name: 'event_delete')]
    public function delete(Event $event): Response {
        if (!$this->isGranted(EventVoter::DELETE, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de supprimer cet événement.");
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        foreach ($event->getParticipants() as $participant) {
            $event->removeParticipant($participant);
        }
        $this->entityManager->remove($event);
        $this->entityManager->flush();
        $this->addFlash('danger', 'Vous avez supprimé l\'événement '.$event->getTitle());

        return $this->redirectToRoute('list_events');
    }

    #[Route('/events/inscription/{id}', name: 'event_inscription')]
    public function inscription(Event $event, UserRepository $repo): Response {
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        if($event->hasRemainingPlaces() and $user) {
            if($event->getPrice()<=0){
                $event->addParticipant($user);
                $user->addEvent($event);
                $this->entityManager->persist($user);
                $this->entityManager->persist($event);
                $this->entityManager->flush();

                $this->mailService->sendEmail($user->getEmail(), 'Inscription à l\'événement', 'Vous vous êtes inscrit à l\'événement ' .$event->getTitle());
                $this->addFlash('success', 'Vous vous êtes inscrit à l\'événement '.$event->getTitle());

                return $this->redirectToRoute('user_events');
            }else{
                return $this->redirectToRoute('payment', [
                    'id' => $event->getId(),
                ]);
            }
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/desinscription/{id}', name: 'event_desinscription')]
    public function desinscription(Event $event, UserRepository $repo): Response {
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        $event->removeParticipant($user);
        $user->removeEvent($event);
        $this->entityManager->persist($user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->mailService->sendEmail($user->getEmail(), 'Désinscription à l\'événement', 'Vous vous êtes désinscrit de l\'événement ' .$event->getTitle());
        $this->addFlash('danger', 'Vous vous êtes désinscrit de l\'événement '.$event->getTitle());

        return $this->redirectToRoute('user_events');
    }

    #[Route('/events/{id}', name: 'event_show')]
    public function show(Event $event): Response {
        // Vérification des accès
        if (!$this->isGranted(EventVoter::VIEW, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de visionner cet événement, veuillez vous connecter.");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events', name: 'list_events')]
    public function listEvents(Request $request): Response
    {
        $title = $request->query->get('title');
        $date = $request->query->get('date');
        $placesRemaining = $request->query->get('placesRemaining');
        $isPublic = $request->query->get('isPublic');

        $page = $request->query->getInt('page', 1);
        $limit = 5;

        // ?pb va retourner un event qui correspond au critères exact pb pour date 
        $events_pagination = $this->repo->findByFilters($title, $date, $placesRemaining, $isPublic, $page, $limit);
        $totalItems = count($events_pagination);
        $pagesCount = ceil($totalItems / $limit);
        // 'events' => $repo->findAllPagination(1, 3)
        return $this->render('events/show_filter_event.html.twig', [
            'events' => $events_pagination,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'currentPage' => $page,
            'filters' => [
                'title' => $title,
                'date' => $date,
                'placesRemaining' => $placesRemaining,
                'isPublic' => $isPublic,
            ],
        ]);
    }
}
