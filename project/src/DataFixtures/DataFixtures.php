<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DataFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager) {
        // Create some users
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setNom('Nom' . $i);
            $user->setPrenom('Prenom' . $i);
            $user->setEmail('user' . $i . '@example.com');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password' . $i));
            $manager->persist($user);
        }

        // Create some events
        for ($j = 1; $j <= 5; $j++) {
            $event = new Event();
            $event->setTitle('Event ' . $j);
            $event->setDescription('Description of event ' . $j);
            $event->setDate(new \DateTime('2024-06-0' . $j . ' 10:00:00'));
            $event->setParticipantsNumber(50);
            $event->setPublic(true);
            $event->setFree(true);

            $manager->persist($event);
        }

        // Assign users to events
        $users = $manager->getRepository(User::class)->findAll();
        $events = $manager->getRepository(Event::class)->findAll();

        foreach ($users as $user) {
            foreach ($events as $event) {
                $event->addParticipant($user);
            }
        }

        $manager->flush();
    }
}
