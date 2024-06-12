<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController {

    #[Route('/', name: 'app_homepage')]
    public function homepage(EventRepository $repo): Response
    {
        $user = $this->getUser(); // Obtenir l'utilisateur authentifié

        return $this->render('events/home.html.twig', [
            'user' => $user,
            'events' => $repo->findAll()
        ]);
    }

    #[Route('/events/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }
        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/inscription/{id}', name: 'event_inscription')]
    public function inscription(Event $event, UserRepository $repo, EntityManagerInterface $entityManager): Response {
        $userInterface = $this->getUser(); // Obtenir l'utilisateur authentifié
        $user = $repo->findByIdentifier($userInterface->getUserIdentifier());

        if($event->hasRemainingPlaces() and $user) {
            $event->addParticipant($user);
            $user->addEvent($event);
            $entityManager->persist($user);
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('user_events');
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}', name: 'event_show')]
    public function show(Event $event): Response {
        // Vérification des accès
        $this->denyAccessUnlessGranted('view_details_event', $event);

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events', name: 'list_events')]
    public function listEvents(EventRepository $repo, Request $request): Response
    {
        $title = $request->query->get('title');
        $date = $request->query->get('date');
        $placesRemaining = $request->query->get('placesRemaining');
        $isPublic = $request->query->get('isPublic');

        $page = $request->query->getInt('page', 1);
        $limit = 5;

        // ?pb va retourner un event qui correspond au critères exact pb pour date 
        $events_pagination = $repo->findByFilters($title, $date, $placesRemaining, $isPublic, $page, $limit);
        $totalItems = count($events_pagination);
        $pagesCount = ceil($totalItems / $limit);
        // 'events' => $repo->findAllPagination(1, 3)
        return $this->render('events/show_filter_event.html.twig', [
            'events' => $events_pagination,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'currentPage' => $page,
        ]);
    }
}
