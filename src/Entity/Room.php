<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'managedRooms')]
    private ?User $roomManager = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Building $building = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    private ?Group $belongsTo = null;

    #[ORM\Column]
    private bool $isPrivate = false;

    #[ORM\OneToMany(mappedBy: 'requestedRoom', targetEntity: Request::class, orphanRemoval: true)]
    private Collection $requests;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'rooms')]
    private Collection $occupants;

    public function __construct()
    {
        $this->requests = new ArrayCollection();
        $this->occupants = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    public function __toString() {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Request>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): static
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->setRequestedRoom($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getRequestedRoom() === $this) {
                $request->setRequestedRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getOccupants(): Collection
    {
        return $this->occupants;
    }

    public function addOccupant(User $occupant): static
    {
        if (!$this->occupants->contains($occupant)) {
            $this->occupants->add($occupant);
            $occupant->addRoom($this);
        }

        return $this;
    }

    public function removeOccupant(User $occupant): static
    {
        if ($this->occupants->removeElement($occupant)) {
            $occupant->removeRoom($this);
        }

        return $this;
    }

    public function getRoomManager(): ?User
    {
        return $this->roomManager;
    }

    public function setRoomManager(?User $roomManager): static
    {
        $this->roomManager = $roomManager;

        return $this;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getBelongsTo(): ?Group
    {
        return $this->belongsTo;
    }

    public function setBelongsTo(?Group $belongsTo): static
    {
        $this->belongsTo = $belongsTo;

        return $this;
    }

    public function isIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): static
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getOccupantsCount(): int
    {
        return $this->occupants->count();
    }
}
