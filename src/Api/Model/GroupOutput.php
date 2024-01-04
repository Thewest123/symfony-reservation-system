<?php

namespace App\Api\Model;

use App\Entity\Group;

class GroupOutput
{
    public function __construct(
        public ?int $id,
        public string $name,
        public int $members,
        public int $rooms_count,
        public string $groupManager
    ) {
    }

    public static function fromEntity(Group $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getName(),
            $entity->getUsersCount(),
            $entity->getRoomsCount(),
            $entity->getGroupManager()->getUsername()
        );
    }
}
