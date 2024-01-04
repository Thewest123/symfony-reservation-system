<?php

namespace App\Api\Model;

use App\Entity\Room;

class RoomOutput
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $room_manager_username,
        public string $group_name,
        public bool $is_private,
        public ?int $occupants_count
    ) {
    }

    public static function fromEntity(Room $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getName(),
            $entity->getRoomManager()->getUsername(),
            $entity->getBelongsTo()->getName(),
            $entity->getIsPrivate(),
            $entity->getOccupantsCount()
        );
    }
}
