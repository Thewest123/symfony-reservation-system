<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Request::class, mappedBy: 'attendees')]
    private Collection $requests;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Request::class, orphanRemoval: true)]
    private Collection $createdRequests;

    #[ORM\ManyToMany(targetEntity: Room::class, inversedBy: 'occupants')]
    private Collection $rooms;

    #[ORM\OneToMany(mappedBy: 'roomManager', targetEntity: Room::class)]
    private Collection $managedRooms;

    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    private Collection $groups;

    #[ORM\OneToMany(mappedBy: 'groupManager', targetEntity: Group::class)]
    private Collection $managedGroups;

    public function __construct()
    {
        $this->createdRequests = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->managedRooms = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->managedGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    /**
     * @return Collection<int, Request>
     */
    public function getCreatedRequests(): Collection
    {
        return $this->createdRequests;
    }

    public function addCreatedRequest(Request $createdRequest): static
    {
        if (!$this->createdRequests->contains($createdRequest)) {
            $this->createdRequests->add($createdRequest);
            $createdRequest->setAuthor($this);
        }

        return $this;
    }

    public function removeCreatedRequest(Request $createdRequest): static
    {
        if ($this->createdRequests->removeElement($createdRequest)) {
            // set the owning side to null (unless already changed)
            if ($createdRequest->getAuthor() === $this) {
                $createdRequest->setAuthor(null);
            }
        }

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
            $request->addAttendee($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            $request->removeAttendee($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        $this->rooms->removeElement($room);

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getManagedRooms(): Collection
    {
        return $this->managedRooms;
    }

    public function addManagedRoom(Room $managedRoom): static
    {
        if (!$this->managedRooms->contains($managedRoom)) {
            $this->managedRooms->add($managedRoom);
            $managedRoom->setRoomManager($this);
        }

        return $this;
    }

    public function removeManagedRoom(Room $managedRoom): static
    {
        if ($this->managedRooms->removeElement($managedRoom)) {
            // set the owning side to null (unless already changed)
            if ($managedRoom->getRoomManager() === $this) {
                $managedRoom->setRoomManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getManagedGroups(): Collection
    {
        return $this->managedGroups;
    }

    public function addManagedGroup(Group $group): static
    {
        if (!$this->managedGroups->contains($group)) {
            $this->managedGroups->add($group);
            $group->setGroupManager($this);
        }

        return $this;
    }

    public function removeManagedGroup(Group $group): static
    {
        if ($this->managedGroups->removeElement($group)) {
            // set the owning side to null (unless already changed)
            if ($group->getGroupManager() === $this) {
                $group->setGroupManager(null);
            }
        }

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
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        $this->groups->removeElement($group);

        return $this;
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
            $this->roles = $roles;

            return $this;
    }

    public function eraseCredentials(): void
    {
//        $this->password = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getGroupsCount(): int
    {
        return $this->groups->count();
    }

    public function getManagedRoomsCount(): int
    {
        return $this->managedRooms->count();
    }
}
