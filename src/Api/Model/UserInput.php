<?php

namespace App\Api\Model;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;


class UserInput
{
    public function __construct(
         #[Assert\Type('string')]
         #[Assert\NotBlank()]
         #[Assert\Length(min: 3, max: 255)]
        public string $username,
         #[Assert\Type('string')]
         #[Assert\NotBlank()]
         #[Assert\Length(min: 4, max: 255)]
        public string $password,
         #[Assert\Type('string')]
         #[Assert\NotBlank()]
         #[Assert\Length(max: 255)]
        public string $name,
    ) {
    }

    public function toEntity(UserPasswordHasherInterface $hasher): User
    {
        $user = new User();
        $user->setUsername($this->username);
        $user->setPassword($hasher->hashPassword($user, $this->password));
        $user->setName($this->name);
        return $user;
    }
}
