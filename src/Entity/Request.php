<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'createdRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'requests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $requestedRoom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan('+0 hours')]
    private ?DateTimeInterface $date = null;

    #[ORM\Column]
    private ?bool $approved = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'requests')]
    private Collection $attendees;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

    public function __toString() {
        return $this->requestedRoom->getName()." ".$this->date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(User $attendee): static
    {
        if (!$this->attendees->contains($attendee)) {
            $this->attendees->add($attendee);
        }

        return $this;
    }

    public function removeAttendee(User $attendee): static
    {
        $this->attendees->removeElement($attendee);

        return $this;
    }

    public function getRequestedRoom(): ?Room
    {
        return $this->requestedRoom;
    }

    public function setRequestedRoom(?Room $requestedRoom): static
    {
        $this->requestedRoom = $requestedRoom;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): static
    {
        $this->approved = $approved;

        return $this;
    }

    public function getAttendeesCount(): int
    {
        return $this->attendees->count();
    }
}
