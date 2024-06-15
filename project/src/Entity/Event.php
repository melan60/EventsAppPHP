<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $participants_number = null;

    #[ORM\Column(options: ["default" => 0])]
    private ?int $price = null;

    #[ORM\Column]
    private ?bool $public = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'createdEvents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $creator;

    public function getCreator(): ?User {
        return $this->creator;
    }

    public function setCreator(?User $creator): self {
        $this->creator = $creator;
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private $participants;

    public function __construct() {
        $this->participants = new ArrayCollection();
    }

    public function getParticipants(): Collection {
        return $this->participants;
    }

    public function addParticipant(User $participant): self {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }
        return $this;
    }

    public function removeParticipant(User $participant): self {
        if($this->participants->removeElement($participant)) {
            $participant->removeEvent($this);
        }
        return $this;
    }

    public function hasParticipant(User $participant): bool {
        return $this->participants->contains($participant);
    }

    public function hasRemainingPlaces(): bool {
        return $this->participants->count() < $this->participants_number;
    }

    public function getReservedPlaces(): int {
        return $this->participants->count();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getParticipantsNumber(): ?int
    {
        return $this->participants_number;
    }

    public function setParticipantsNumber(int $participants_number): static
    {
        $this->participants_number = $participants_number;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;

        return $this;
    }
}
