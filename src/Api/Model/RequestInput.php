<?php

namespace App\Api\Model;
use App\Entity\Request;
use Symfony\Component\Validator\Constraints as Assert;

class RequestInput
{
    public function __construct(
        #[Assert\Type('string')]
        #[Assert\NotBlank()]
        #[Assert\Length(min: 3, max: 255)]
        public string $requested_room_name,
        #[Assert\NotBlank]
        public \DateTimeInterface $date_from,
        #[Assert\NotBlank]
        public \DateTimeInterface $date_to)
    {
    }
    public function toEntity(): Request
    {
        $request = new Request();
        return $request;
    }
}