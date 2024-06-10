<?php

namespace App\Security;

use App\Entity\Event;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    const VIEW = 'view_details_event';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject) : bool{
        return in_array($attribute, [self::VIEW])
            && $subject instanceof \App\Entity\Event;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token) : bool{
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false; // L'utilisateur doit être connecté pour voir les événements privés
        }

        // L'utilisateur peut voir les événements publics ou les événements privés s'il est connecté
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
        }

        return false;
    }

    private function canView(Event $event, $user) {
        if ($event->isPublic()) {
            return true; // Tout le monde peut voir les événements publics
        }

        return $user !== null; // Seuls les utilisateurs connectés peuvent voir les événements privés
    }
}