<?php

namespace App\Security\Voter;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GroupVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const MANAGE = 'manage';

    public function __construct(
        private Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::MANAGE])) {
            return false;
        }

        // only vote on `Group` objects
        if (!$subject instanceof Group) {
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

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::MANAGE => $this->canManage($subject, $user)
        };
    }


    private function canView(Group $group, User $user): bool
    {
        if ($this->canEdit($group, $user))
            return true;

        $groups = [$group];
        while ($group?->getParent() !== null && !in_array($group = $group->getParent(), $groups)) {
            array_push($groups, $group);
        }

        // Check if any group allows the user to manage the room
        foreach ($groups as $key => $group)
            if ($group?->getUsers()->contains($user))
                return true;

        return false;
    }

    private function canManage(Group $group, User $user): bool
    {
        if ($this->canEdit($group, $user))
            return true;

        if ($group->getGroupManager() === $user)
            return true;

        return false;
    }

    private function canEdit(Group $group, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}