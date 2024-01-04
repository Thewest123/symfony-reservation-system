<?php

namespace App\Api\Model;

use App\Entity\Request;

class RequestOutput
{
    public function __construct(
        public ?int $id,
        public string $author,
        public string $requested_room_name,
        public \DateTimeInterface $date_from,
        public \DateTimeInterface $date_to,
        public bool $approved,
        public array $users) {}
    public static function fromEntity(Request $entity): self
    {
        $usernames = [];
        foreach ($entity->getAttendees() as $attendee )
        {
            $usernames [] = $attendee->getUsername();
        }

        return new self(
            $entity->getId(),
            $entity->getAuthor()->getUsername(),
            $entity->getRequestedRoom()->getName(),
            $entity->getDate(),
            $entity->getEndDate(),
            $entity->isApproved(),
            $usernames
        );
    }

}