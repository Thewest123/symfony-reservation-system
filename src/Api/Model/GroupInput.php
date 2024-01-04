<?php

namespace App\Api\Model;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class GroupInput
{
    public function __construct(
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 255)]
    public string $name,
    #[Assert\NotNull()]
    public int $group_manager_id ) {
    }
    public function toEntity(User $groupManager): Group
    {
        $group = new Group();
        $group->setName($this->name);
        $group->setGroupManager($groupManager);
        return $group;
    }
}