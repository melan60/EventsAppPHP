<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Il y a déjà un utilisateur avec cette adresse email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
//    #[Email(
//        message: '"{{ value }}" est invalide.',
//    )]
//    #[Regex(
//        pattern: "/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,6}$/",
//        message: 'Ce champ doit être au format xxx@yyy.zz',
//        match: true,
//    )]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'creator', cascade: ['remove'])]
    private $createdEvents;

    public function getCreatedEvents(): Collection {
        return $this->createdEvents;
    }

    public function addCreatedEvent(Event $event): self {
        $this->createdEvents->add($event);
        return $this;
    }

    public function removeCreatedEvent(Event $event): self {
        $this->createdEvents->removeElement($event);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private $events;

    public function __construct() {
        $this->events = new ArrayCollection();
        $this->createdEvents = new ArrayCollection();
    }

    public function getEvents(): Collection {
        return $this->events;
    }

    public function addEvent(Event $event): self {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addParticipant($this);
        }
        return $this;
    }

    public function removeEvent(Event $event): self {
        $this->events->removeElement($event);
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function __toString() : String {
        return $this->prenom.' '.$this->nom. ' '. $this->email. ' ' .$this->password;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
