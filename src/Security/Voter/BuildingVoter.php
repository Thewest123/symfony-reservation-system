<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Building;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class BuildingVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on `Building` objects
        if (!$subject instanceof Building) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user)
        };
    }


    private function canView(Building $building, User $user): bool
    {
        if ($this->canEdit($building, $user))
            return true;

        // TODO

        return true;
    }

    private function canEdit(Building $building, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}