<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DataFixtures extends Fixture {
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager) {
        $userNoms = ["test", "az", "Yo", "admin", "user1"];
        $userPrenoms = ["test", "er", "Yo", "admin", "user1"];
        $userMails = ["test@test.com", "az@er.com", "yo@yo.com", "admin@admin.com", "user1@example.com"];
        $userPasswords = ["test1234", "azerty123", "yoyo1234", 'admin987', 'password1'];

        $users = [];
        for ($i = 0; $i < count($userNoms); $i++) {
            $user = new User();
            $user->setNom($userNoms[$i]);
            $user->setPrenom($userPrenoms[$i]);
            $user->setEmail($userMails[$i]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userPasswords[$i]));
            $manager->persist($user);
            $users[] = $user;
        }

        $eventsTitle = [
            "Initiation boxe", "Initiation volley", "Initiation self-defense", "Initiation rugby", "Initiation aviron",
            "Initiation danse", "Initiation escrime", "Initiation tir à l'arc", "Initiation natation", "Initiation escalade",
            "Initiation yoga", "Initiation karaté", "Initiation judo", "Initiation tennis", "Initiation ping-pong",
            "Initiation gymnastique", "Initiation crossfit", "Initiation parkour", "Initiation cyclisme", "Initiation athlétisme",
            "Initiation patinage", "Initiation ski", "Initiation snowboard", "Initiation course à pied", "Initiation haltérophilie"
        ];

        $eventsDescription = [
            "Boxe", "Volley", "Self-defense", "Rugby", "Aviron",
            "Danse", "Escrime", "Tir à l'arc", "Natation", "Escalade",
            "Yoga", "Karaté", "Judo", "Tennis", "Ping-pong",
            "Gymnastique", "Crossfit", "Parkour", "Cyclisme", "Athlétisme",
            "Patinage", "Ski", "Snowboard", "Course à pied", "Haltérophilie"
        ];

        $eventsNbParticipants = [
            5, 12, 2, 22, 10,
            15, 8, 6, 20, 18,
            25, 7, 11, 30, 4,
            9, 14, 19, 13, 16,
            21, 17, 3, 24, 23
        ];

        $eventsPublic = [
            false, true, true, false, true,
            true, false, true, false, true,
            false, true, true, false, true,
            true, false, true, false, true,
            false, true, true, false, true
        ];

        $eventsPrice = [
            0, 0, 0, 10, 20,
            15, 5, 10, 8, 12,
            6, 0, 0, 7, 25,
            10, 20, 30, 15, 18,
            22, 24, 10, 5, 0
        ];


        $userAdmin = $users[3];

        for ($i = 0; $i < count($eventsTitle); $i++) {
            $event = new Event();
            $event->setTitle($eventsTitle[$i]);
            $event->setDescription($eventsDescription[$i]);
            $event->setDate(new \DateTime('2024-06-30 10:00:00'));
            $event->setCreator($userAdmin);
            $event->setPrice($eventsPrice[$i]);
            $event->setPublic($eventsPublic[$i]);
            $event->setParticipantsNumber($eventsNbParticipants[$i]);

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
