<?php

namespace App\Security\Voter;

use App\Entity\Room;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class RoomVoter extends Voter
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

        // only vote on `Room` objects
        if (!$subject instanceof Room) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user is not authenticated, e.g. only allow them to
            // see public rooms
            if ($attribute === self::VIEW)
                return !$subject->getIsPrivate();
            else
                return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::MANAGE => $this->canManage($subject, $user)
        };
    }


    private function canView(Room $room, User $user): bool
    {
        if ($this->canManage($room, $user))
            return true;

        // TODO

        return true;
        //return false;
    }

    private function canManage(Room $room, User $user): bool
    {
        if ($this->canEdit($room, $user))
            return true;

        if ($room->getRoomManager() === $user)
            return true;

        // Get all groups and parent groups of the room
        $group = $room->getBelongsTo();
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

    private function canEdit(Room $room, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}