<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'managedGroups')]
    private ?User $groupManager = null;

    #[ORM\OneToMany(mappedBy: 'belongsTo', targetEntity: Room::class)]
    private Collection $rooms;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subgroups')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $subgroups;

    public function __construct()
    {
        $this->subgroups = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupManager(): ?User
    {
        return $this->groupManager;
    }

    public function setGroupManager(?User $groupManager): static
    {
        $this->groupManager = $groupManager;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubgroups(): Collection
    {
        return $this->subgroups;
    }

    public function addSubgroup(self $subgroup): static
    {
        if (!$this->subgroups->contains($subgroup)) {
            $this->subgroups->add($subgroup);
            $subgroup->setParent($this);
        }

        return $this;
    }

    public function removeSubgroup(self $subgroup): static
    {
        if ($this->subgroups->removeElement($subgroup)) {
            // set the owning side to null (unless already changed)
            if ($subgroup->getParent() === $this) {
                $subgroup->setParent(null);
            }
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
            $room->setBelongsTo($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getBelongsTo() === $this) {
                $room->setBelongsTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addGroup($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeGroup($this);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function getRoomsCount(): int
    {
        return $this->rooms->count();
    }

    public function getUsersCount(): int
    {
        return $this->users->count();
    }
}
