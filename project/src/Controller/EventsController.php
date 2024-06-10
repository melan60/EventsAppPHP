<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController {

    #[Route('/home', name: 'app_homepage')]
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

    #[Route('/events/{id}', name: 'event_show')]
    public function show(Event $event): Response
    {
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

        // ?pb va retourner un event qui correspond au critères exact pb pour date 
        $events = $repo->findByFilters($title, $date, $placesRemaining, $isPublic);

        return $this->render('events/show_filter_event.html.twig', [
            'events' => $events,

        ]);
    }
}
