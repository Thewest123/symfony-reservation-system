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

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Request::class, orphanRemoval: true)]
    private Collection $requests;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'rooms')]
    private Collection $occupants;

    #[ORM\ManyToOne(inversedBy: 'managedRooms')]
    private ?User $manager = null;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Group::class)]
    private Collection $groups;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Building $building = null;

    public function __construct()
    {
        $this->requests = new ArrayCollection();
        $this->occupants = new ArrayCollection();
        $this->groups = new ArrayCollection();
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
            $request->setRoom($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getRoom() === $this) {
                $request->setRoom(null);
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

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->setRoom($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            // set the owning side to null (unless already changed)
            if ($group->getRoom() === $this) {
                $group->setRoom(null);
            }
        }

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
}
