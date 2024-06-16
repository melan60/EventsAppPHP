<?php

namespace App\Service;

use App\Entity\Event;

class PlacesRestantesService {
    public function calculPlacesRestantes(Event $event): int {
        $totalPlaces = $event->getParticipantsNumber();
        $nbParticipants = $event->getParticipants()->count();

        return $totalPlaces - $nbParticipants;
    }

}