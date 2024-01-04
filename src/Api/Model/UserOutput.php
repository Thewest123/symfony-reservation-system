<?php

namespace App\Api\Model;

use App\Entity\User;

class UserOutput
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $username,
        public array $roles
    ) {
    }

    public static function fromEntity(User $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getName(),
            $entity->getUsername(),
            $entity->getRoles()
        );
    }
}
