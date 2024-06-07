<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController {
    #[Route('/home', name: 'home')]
    public function index(EventRepository $repo): Response {
        return $this->render('events/home.html.twig', [
            'events' => $repo->findAll()
        ]);
    }

    #[Route('/event/{id}', name: 'event_show')]
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
