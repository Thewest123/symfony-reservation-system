<?php

namespace App\Security\Voter;

use App\Entity\Request;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class RequestVoter extends Voter
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

        // only vote on `Request` objects
        if (!$subject instanceof Request) {
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


    private function canView(Request $request, User $user): bool
    {
        if ($this->canEdit($request, $user))
            return true;

        // If user is an attendee of the request, they can view it
        if ($request->getAttendees()->contains($user))
            return true;

        return false;
    }

    private function canManage(Request $request, User $user): bool
    {
        if (!$this->security->isGranted(RoomVoter::MANAGE, $request->getRequestedRoom()))
            return false;

        if ($this->canEdit($request, $user))
            return true;

        return false;
    }

    private function canEdit(Request $request, User $user): bool
    {
        if ($request->getAuthor() === $user)
            return true;

        $room = $request->getRequestedRoom();

        // If the user is a manager of the room, they can edit the request
        if ($this->security->isGranted(RoomVoter::MANAGE, $room))
            return true;

        return $this->security->isGranted('ROLE_ADMIN');
    }
}