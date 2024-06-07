<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
