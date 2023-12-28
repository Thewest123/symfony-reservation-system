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

    #[ORM\ManyToOne(inversedBy: 'groups')]
    private ?User $manager = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subgroups')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $subgroups;

    #[ORM\ManyToOne(inversedBy: 'groups')]
    private ?Room $room = null;

    public function __construct()
    {
        $this->subgroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }
}
