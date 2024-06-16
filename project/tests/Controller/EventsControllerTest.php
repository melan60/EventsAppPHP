<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventsControllerTest extends WebTestCase
{
    private $originalExceptionHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalExceptionHandler = set_exception_handler(null);
        restore_exception_handler();
    }

    protected function tearDown(): void
    {
        set_exception_handler($this->originalExceptionHandler);
        parent::tearDown();
    }

    public function testHomepage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Événements');
    }

    public function testNewEvent()
    {
        $client = static::createClient();

        // Simulate user authentication
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/events/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Valider')->form([
            'event[title]' => 'Test Event',
            'event[description]' => 'This is a test event.',
            'event[date]' => '2024-06-20 10:00:00',
            'event[price]' => 0,
            'event[public]' => true,
            'event[participants_number]' => 10,
        ]);

        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect()
        );

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Vous avez créé l\'événement Test Event');
    }

    private function getUser()
    {
        return static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
    }

    public function testEditEvent()
    {
        $client = static::createClient();

        // Simulate user authentication
        $client->loginUser($this->getUser());

        // Create a test event
        $event = $this->createTestEvent();

        $crawler = $client->request('GET', '/events/'.$event->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Valider')->form([
            'event[title]' => 'Updated Event Title',
            'event[description]' => 'Updated description.',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/events/'.$event->getId());

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Vous avez modifié l\'événement Updated Event Title');
    }

    private function createTestEvent(): Event
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $event = new Event();
        $event->setTitle('Test Event');
        $event->setDescription('This is a test event.');
        $event->setDate(new \DateTime('2024-06-20 10:00:00'));
        $event->setPrice(0);
        $event->setPublic(true);
        $event->setParticipantsNumber(10);
        $event->setCreator($this->getUser());

        $entityManager->persist($event);
        $entityManager->flush();

        return $event;
    }
}
